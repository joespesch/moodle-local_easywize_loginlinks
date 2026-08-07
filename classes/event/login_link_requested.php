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

namespace local_easywize_loginlinks\event;

/**
 * Event: a login link was requested (created or extended) for a user.
 *
 * Triggered once per user for every successful issuance through the
 * local_easywize_loginlinks_get_login_links web service, so the standard
 * log records who requested a link for which user and when. The event's
 * userid is the requesting (web service) user, relateduserid the user the
 * link logs in, objectid the user_private_key record; other.validuntil
 * holds the key's expiry timestamp.
 *
 * @package    local_easywize_loginlinks
 * @copyright  2026 Köln-Bonner Akademie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class login_link_requested extends \core\event\base {

    /**
     * Initialise the event data.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'user_private_key';
    }

    /**
     * Localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventloginlinkrequested', 'local_easywize_loginlinks');
    }

    /**
     * Human-readable description for the log.
     *
     * @return string
     */
    public function get_description() {
        $validuntil = userdate((int)($this->other['validuntil'] ?? 0));
        return "The user with id '{$this->userid}' requested a one-time login link "
                . "for the user with id '{$this->relateduserid}', valid until {$validuntil}.";
    }

    /**
     * Validate that the event carries the data the log needs.
     *
     * @throws \coding_exception
     */
    protected function validate_data() {
        parent::validate_data();
        if (empty($this->relateduserid)) {
            throw new \coding_exception('The \'relateduserid\' must be set.');
        }
        if (!isset($this->other['validuntil'])) {
            throw new \coding_exception('The \'validuntil\' value must be set in other.');
        }
    }

    /**
     * No restore mapping: keys are not backed up.
     *
     * @return array
     */
    public static function get_objectid_mapping() {
        return ['db' => 'user_private_key', 'restore' => \core\event\base::NOT_MAPPED];
    }

    /**
     * No restore mapping for other fields.
     *
     * @return array|false
     */
    public static function get_other_mapping() {
        return false;
    }
}
