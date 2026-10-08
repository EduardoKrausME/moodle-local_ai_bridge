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
 * settings.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_ai_bridge', get_string('pluginname', 'local_ai_bridge'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configselect(
        'local_ai_bridge/tenantkey',
        get_string('setting:tenantkey', 'local_ai_bridge'),
        get_string('setting:tenantkey_desc', 'local_ai_bridge'),
        'institution_department',
        [
            'institution' => get_string('tenantkey:institution', 'local_ai_bridge'),
            'department' => get_string('tenantkey:department', 'local_ai_bridge'),
            'institution_department' => get_string('tenantkey:institution_department', 'local_ai_bridge'),
        ]
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_ai_bridge/autocreatetenants',
        get_string('setting:autocreatetenants', 'local_ai_bridge'),
        get_string('setting:autocreatetenants_desc', 'local_ai_bridge'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_ai_bridge/logfailures',
        get_string('setting:logfailures', 'local_ai_bridge'),
        get_string('setting:logfailures_desc', 'local_ai_bridge'),
        1
    ));

    $ADMIN->add('localplugins', new admin_externalpage(
        'local_ai_bridge_manage',
        get_string('manage', 'local_ai_bridge'),
        new moodle_url('/local/ai_bridge/manage.php'),
        'local/ai_bridge:manageall'
    ));
}
