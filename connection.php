<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_login();
$tenantid = required_param('tenantid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
\local_ai_bridge\access::require_manage_tenant($tenantid);
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$record = $id ? $DB->get_record('local_ai_bridge_connection', ['id' => $id, 'tenantid' => $tenantid], '*', MUST_EXIST) : null;
$bridgename = $record ? $record->bridge : optional_param('bridge', '', PARAM_ALPHANUMEXT);
$bridges = \local_ai_bridge\bridge_manager::get_bridges();
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/ai_bridge/connection.php', ['tenantid' => $tenantid, 'id' => $id]));
$PAGE->set_title(get_string($id ? 'editconnection' : 'addconnection', 'local_ai_bridge'));
$PAGE->set_heading(format_string($tenant->name));
if ($bridgename === '') {
    echo $OUTPUT->header(); echo $OUTPUT->heading(get_string('chooseprovider', 'local_ai_bridge'));
    foreach ($bridges as $name => $bridge) {
        $url = new moodle_url('/local/ai_bridge/connection.php', ['tenantid' => $tenantid, 'bridge' => $name]);
        echo html_writer::div(html_writer::link($url, $bridge->get_name(), ['class' => 'btn btn-outline-primary']) . html_writer::tag('p', $bridge->get_description()), 'mb-3');
    }
    echo $OUTPUT->footer(); exit;
}
$bridge = \local_ai_bridge\bridge_manager::get_bridge($bridgename);
$existingconfig = $record ? \local_ai_bridge\bridge_manager::decrypt_config((string)$record->configdata) : [];
$form = new \local_ai_bridge\form\connection_form(null, ['bridge' => $bridge, 'existingconfig' => $existingconfig]);
$defaults = $record ? (array)$record : ['tenantid' => $tenantid, 'bridge' => $bridgename, 'enabled' => 1];
$configdefaults = $record ? $existingconfig : $bridge->get_default_config();
foreach ($configdefaults as $key => $value) {
    if (!in_array($key, $bridge->get_secret_fields(), true)) { $defaults['bridgeconfig_' . $key] = $value; }
}
$defaults['bridge'] = $bridgename; $form->set_data($defaults);
if ($form->is_cancelled()) { redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'connections'])); }
if ($data = $form->get_data()) {
    $config = $existingconfig;
    foreach ((array)$data as $key => $value) {
        if (str_starts_with($key, 'bridgeconfig_')) {
            $name = substr($key, 13);
            if ($value !== '' || !in_array($name, $bridge->get_secret_fields(), true)) { $config[$name] = $value; }
        }
    }
    $now = time();
    if ($record) {
        $record->name = $data->name; $record->configdata = \local_ai_bridge\bridge_manager::encrypt_config($config);
        $record->enabled = $data->enabled ? 1 : 0; $record->timemodified = $now; $DB->update_record('local_ai_bridge_connection', $record);
    } else {
        $DB->insert_record('local_ai_bridge_connection', (object)[
            'tenantid' => $tenantid, 'name' => $data->name, 'bridge' => $bridgename,
            'configdata' => \local_ai_bridge\bridge_manager::encrypt_config($config), 'enabled' => $data->enabled ? 1 : 0,
            'timecreated' => $now, 'timemodified' => $now,
        ]);
    }
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'connections']), get_string('changessaved'));
}
echo $OUTPUT->header(); $form->display(); echo $OUTPUT->footer();
