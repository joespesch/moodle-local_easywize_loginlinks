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
 * English language strings for local_easywize_loginlinks.
 *
 * @package    local_easywize_loginlinks
 * @copyright  2026 Köln-Bonner Akademie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Easywize login links';
$string['defaultvalidity'] = 'Default login link validity';
$string['defaultvalidity_desc'] = 'Validity of login links issued by the web service local_easywize_loginlinks_get_login_links when the caller does not pass a validity. The service ignores auth/userkey keylifetime and sets the lifetime by parameter (default is 7 days); keylifetime keeps governing auth_userkey\'s own functions unchanged.';
$string['eventloginlinkrequested'] = 'Login link requested';
$string['maxvalidity'] = 'Maximum login link validity';
$string['maxvalidity_desc'] = 'Upper cap for the validity of login links issued by the web service. Validity values requested via the web service parameter are limited to this duration. Independently of this setting, the code enforces a hard upper bound of 60 days: this setting can only lower that bound, never raise it.';
$string['privacy:metadata'] = 'The Easywize login links plugin only creates auth_userkey login keys, which are stored by Moodle core (user_private_key). It does not store any personal data itself.';
