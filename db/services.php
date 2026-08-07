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
 * External function definitions for local_easywize_loginlinks.
 *
 * @package    local_easywize_loginlinks
 * @copyright  2026 Köln-Bonner Akademie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
        'local_easywize_loginlinks_get_login_links' => [
                'classname'   => 'local_easywize_loginlinks\external\get_login_links',
                'methodname'  => 'execute',
                'classpath'   => '',
                'description' => 'Create or extend one-time login links (auth_userkey) for the given users. '
                        . 'Ignores auth/userkey keylifetime and sets the lifetime by parameter '
                        . '(default is 7 days, capped at a configurable maximum with a hard code limit of 60 days). '
                        . 'Users with administrative privileges are refused. Every issuance is logged.',
                'type'        => 'write',
                'capabilities'=> 'auth/userkey:generatekey',
                'ajax'        => false,
        ],
];
