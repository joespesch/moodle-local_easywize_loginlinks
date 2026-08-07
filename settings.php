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

/**
 * Admin settings for local_easywize_loginlinks.
 *
 * @package    local_easywize_loginlinks
 * @copyright  2026 Köln-Bonner Akademie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_easywize_loginlinks',
            get_string('pluginname', 'local_easywize_loginlinks'));

    // Deliberately placed in the authentication category, next to the auth_userkey
    // settings, so the link lifetimes are visible where admins reason about login
    // security (see the keylifetime discussion in the plugin description).
    $ADMIN->add('authsettings', $settings);

    $settings->add(new admin_setting_configduration('local_easywize_loginlinks/defaultvalidity',
            get_string('defaultvalidity', 'local_easywize_loginlinks'),
            get_string('defaultvalidity_desc', 'local_easywize_loginlinks'),
            7 * DAYSECS, DAYSECS));

    $settings->add(new admin_setting_configduration('local_easywize_loginlinks/maxvalidity',
            get_string('maxvalidity', 'local_easywize_loginlinks'),
            get_string('maxvalidity_desc', 'local_easywize_loginlinks'),
            60 * DAYSECS, DAYSECS));
}
