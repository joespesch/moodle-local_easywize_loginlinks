<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_easywize_loginlinks\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;
use context_system;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * Web service that creates (or extends) one-time login links for users.
 *
 * The links are auth_userkey login URLs. This service deliberately ignores
 * auth/userkey keylifetime and sets the lifetime by parameter (default is
 * 7 days): keylifetime keeps governing auth_userkey's own flows (e.g.
 * auth_userkey_request_login_url) unchanged, while links issued here are
 * meant for delivery via SMS or email, where a lifetime of seconds is
 * useless. Default and maximum lifetime are visible admin settings of this
 * plugin (placed in the authentication settings category, next to
 * auth_userkey), so each configured value means exactly what it says.
 *
 * Key handling: one key per user (script 'auth/userkey', instance = userid).
 * An existing key is reused and its validity only ever extended, never
 * shortened, so every delivered link of a user is the same link. Because
 * auth_userkey deletes all keys of a user on the first successful login
 * (user_login_userkey()), that first click invalidates all outstanding links
 * of this user at once — an intended security property: a link that has been
 * used once cannot be replayed from an old SMS or email.
 *
 * Callers need the capability auth/userkey:generatekey in system context;
 * auth_userkey must be installed and enabled as auth method.
 *
 * Security constraints: MAX_VALID_SECONDS is a hard upper bound enforced in
 * code — the maxvalidity admin setting can only lower it, never raise it.
 * Users with administrative privileges (site admins or moodle/site:config)
 * never get a link. Every issued or extended link triggers the
 * login_link_requested event, so the standard log records who requested a
 * link for which user and when.
 *
 * Per-user problems (unknown email, suspended user, administrative user, ...)
 * are reported as a per-item warning instead of failing the whole batch.
 *
 * @package local_easywize_loginlinks
 * @copyright 2026 Köln-Bonner Akademie
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_login_links extends external_api {

    /** @var int Fallback default link validity in seconds (7 days) if the setting is unset. */
    const DEFAULT_VALID_SECONDS = 7 * DAYSECS;

    /**
     * @var int Hard upper bound for link validity in seconds (60 days, ~2 months).
     * Enforced in code: the maxvalidity admin setting can configure a lower
     * maximum, but values above this bound (or an unset setting) fall back to it.
     */
    const MAX_VALID_SECONDS = 60 * DAYSECS;

    /**
     * Define parameters for the service.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
                'users' => new external_multiple_structure(
                        new external_single_structure([
                                'userid' => new external_value(PARAM_INT,
                                        'User id (takes precedence over email)', VALUE_DEFAULT, 0),
                                'email' => new external_value(PARAM_RAW,
                                        'User email, used if userid is 0', VALUE_DEFAULT, ''),
                        ]), 'Users to create or extend login links for'
                ),
                'validhours' => new external_value(PARAM_INT,
                        'Link validity in hours, applied to all users of this call. 0 or omitted = '
                        . 'use the default validity from the plugin settings (7 days unless changed). '
                        . 'Values above the configured maximum are capped; the maximum itself is '
                        . 'hard-limited to 60 days in code. '
                        . 'An existing key with a longer validity is kept as is.',
                        VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Create or extend the login links.
     *
     * @param array $users List of ['userid' => int, 'email' => string].
     * @param int $validhours Validity in hours for all links of this call (0 = configured default).
     * @return array
     */
    public static function execute($users, $validhours = 0) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::execute_parameters(), compact('users', 'validhours'));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('auth/userkey:generatekey', $context);

        if (!is_enabled_auth('userkey')) {
            throw new \moodle_exception('pluginisdisabled', 'auth_userkey');
        }

        // Requested lifetime: WS parameter first, plugin default otherwise, capped at the
        // configured maximum. The configduration settings store seconds.
        $defaultseconds = (int) get_config('local_easywize_loginlinks', 'defaultvalidity');
        if ($defaultseconds <= 0) {
            $defaultseconds = self::DEFAULT_VALID_SECONDS;
        }
        // MAX_VALID_SECONDS is a hard ceiling: the admin setting may only lower it.
        $maxseconds = (int) get_config('local_easywize_loginlinks', 'maxvalidity');
        if ($maxseconds <= 0 || $maxseconds > self::MAX_VALID_SECONDS) {
            $maxseconds = self::MAX_VALID_SECONDS;
        }
        $requestedseconds = (int) $params['validhours'] * HOURSECS;
        if ($requestedseconds <= 0) {
            $requestedseconds = $defaultseconds;
        }
        $validuntil = time() + min($requestedseconds, $maxseconds);

        $links = [];
        foreach ($params['users'] as $requested) {
            $requesteduserid = (int)($requested['userid'] ?? 0);
            $requestedemail = trim((string)($requested['email'] ?? ''));

            $result = [
                'userid' => 0,
                'email' => $requestedemail,
                'loginurl' => '',
                'validuntil' => 0,
                'warning' => '',
            ];

            // Resolve the user: explicit id wins, otherwise lookup by email.
            $user = false;
            if ($requesteduserid > 0) {
                $user = $DB->get_record('user', ['id' => $requesteduserid, 'deleted' => 0], '*', IGNORE_MISSING);
                if (!$user) {
                    $result['warning'] = "No active user with id {$requesteduserid}.";
                }
            } else if ($requestedemail !== '') {
                $matches = $DB->get_records('user', ['email' => $requestedemail, 'deleted' => 0], 'id', '*', 0, 2);
                if (count($matches) === 1) {
                    $user = reset($matches);
                } else if (count($matches) > 1) {
                    $result['warning'] = "Email {$requestedemail} is ambiguous (multiple users).";
                } else {
                    $result['warning'] = "No active user with email {$requestedemail}.";
                }
            } else {
                $result['warning'] = 'Neither userid nor email given.';
            }

            if ($user) {
                if (!empty($user->suspended)) {
                    $result['warning'] = "User {$user->id} is suspended and cannot log in.";
                } else if (is_siteadmin($user) || has_capability('moodle/site:config', $context, $user)) {
                    // Never hand out password-less login links for accounts with
                    // administrative privileges.
                    $result['warning'] = "User {$user->id} has administrative privileges; no login link is issued.";
                } else {
                    $key = self::get_or_extend_userkey((int)$user->id, $validuntil);
                    $result['userid'] = (int)$user->id;
                    $result['email'] = (string)$user->email;
                    $result['loginurl'] = $CFG->wwwroot . '/auth/userkey/login.php?key=' . $key->value;
                    $result['validuntil'] = (int)$key->validuntil;

                    \local_easywize_loginlinks\event\login_link_requested::create([
                        'context' => $context,
                        'objectid' => (int)$key->id,
                        'relateduserid' => (int)$user->id,
                        'other' => ['validuntil' => (int)$key->validuntil],
                    ])->trigger();
                }
            }

            $links[] = $result;
        }

        return ['links' => $links];
    }

    /**
     * Fetch the user's auth_userkey key, extending its validity if needed,
     * or create a new one. Validity is never shortened.
     *
     * @param int $userid User id.
     * @param int $validuntil Requested expiry timestamp.
     * @return \stdClass Key record (id, value, validuntil).
     */
    protected static function get_or_extend_userkey(int $userid, int $validuntil): \stdClass {
        global $DB;

        $now = time();
        $conditions = [
            'script' => 'auth/userkey',
            'userid' => $userid,
            'instance' => $userid,
        ];

        // A transaction guards against races with parallel requests writing the same key.
        $tx = $DB->start_delegated_transaction();
        if ($record = $DB->get_record('user_private_key', $conditions, '*', IGNORE_MISSING)) {
            if ($validuntil > (int)$record->validuntil) {
                $record->validuntil = $validuntil;
                $record->timemodified = $now;
                $DB->update_record('user_private_key', $record);
            }
            $keyid = (int)$record->id;
        } else {
            do {
                $value = bin2hex(random_bytes(16));
            } while ($DB->record_exists('user_private_key', ['value' => $value]));
            $keyid = (int)$DB->insert_record('user_private_key', (object)[
                'script' => 'auth/userkey',
                'userid' => $userid,
                'instance' => $userid,
                'value' => $value,
                'validuntil' => $validuntil,
                'iprestriction' => '',
                'timecreated' => $now,
            ]);
        }
        $tx->allow_commit();

        return $DB->get_record('user_private_key', ['id' => $keyid], 'id, value, validuntil', MUST_EXIST);
    }

    /**
     * Define the return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
                'links' => new external_multiple_structure(
                        new external_single_structure([
                                'userid' => new external_value(PARAM_INT, 'Resolved user id (0 if not resolved)'),
                                'email' => new external_value(PARAM_RAW, 'User email (as requested or resolved)'),
                                'loginurl' => new external_value(PARAM_RAW, 'One-time login URL (empty on warning)'),
                                'validuntil' => new external_value(PARAM_INT, 'Expiry unix timestamp (0 on warning)'),
                                'warning' => new external_value(PARAM_RAW, 'Per-user problem description, empty on success'),
                        ]), 'One result per requested user, in request order'
                ),
        ]);
    }
}
