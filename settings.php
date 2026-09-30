<?php
// This file is part of Moodle - http://moodle.org/

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
    $settings->add(new admin_setting_configtextarea(
        'local_ai_bridge/allowedhosts',
        get_string('setting:allowedhosts', 'local_ai_bridge'),
        get_string('setting:allowedhosts_desc', 'local_ai_bridge'),
        "api.openai.com\napi.anthropic.com\ngenerativelanguage.googleapis.com",
        PARAM_RAW_TRIMMED
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
