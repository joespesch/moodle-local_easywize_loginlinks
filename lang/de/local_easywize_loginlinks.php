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
 * German language strings for local_easywize_loginlinks.
 *
 * @package    local_easywize_loginlinks
 * @copyright  2026 Köln-Bonner Akademie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Easywize Login-Links';
$string['defaultvalidity'] = 'Standard-Gültigkeit der Login-Links';
$string['defaultvalidity_desc'] = 'Gültigkeit der Login-Links, die der Webservice local_easywize_loginlinks_get_login_links ausstellt, wenn der Aufrufer keine Gültigkeit übergibt. Der Service ignoriert auth/userkey keylifetime und setzt die Gültigkeit per Parameter (Standard: 7 Tage); keylifetime gilt unverändert für die eigenen Funktionen von auth_userkey.';
$string['eventloginlinkrequested'] = 'Login-Link angefordert';
$string['maxvalidity'] = 'Maximale Gültigkeit der Login-Links';
$string['maxvalidity_desc'] = 'Obergrenze für die Gültigkeit der vom Webservice ausgestellten Login-Links. Über den Webservice-Parameter angeforderte Gültigkeiten werden auf diese Dauer begrenzt. Unabhängig von dieser Einstellung erzwingt der Code eine harte Obergrenze von 60 Tagen: Diese Einstellung kann die Grenze nur senken, nicht anheben.';
$string['privacy:metadata'] = 'Das Plugin Easywize Login-Links erzeugt lediglich auth_userkey-Login-Schlüssel, die vom Moodle-Kern gespeichert werden (user_private_key). Es speichert selbst keine personenbezogenen Daten.';
