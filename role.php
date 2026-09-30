<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_login();
$tenantid = required_param('tenantid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
\local_ai_bridge\access::require_manage_tenant($tenantid);
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$record = $id ? $DB->get_record('local_ai_bridge_role', ['id' => $id, 'tenantid' => $tenantid], '*', MUST_EXIST) : null;
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/ai_bridge/role.php', ['tenantid' => $tenantid, 'id' => $id]));
$PAGE->set_title(get_string($id ? 'editrole' : 'addrole', 'local_ai_bridge'));
$PAGE->set_heading(format_string($tenant->name));
$form = new \local_ai_bridge\form\role_form();
$form->set_data($record ?: ['tenantid' => $tenantid, 'enabled' => 1]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'roles']));
}
if ($data = $form->get_data()) {
    $transaction = $DB->start_delegated_transaction();
    if (!empty($data->isdefault)) {
        $DB->set_field('local_ai_bridge_role', 'isdefault', 0, ['tenantid' => $tenantid]);
    }
    $now = time();
    if ($id) {
        $record->name = $data->name; $record->idnumber = $data->idnumber;
        $record->isdefault = $data->isdefault ? 1 : 0; $record->enabled = $data->enabled ? 1 : 0;
        $record->timemodified = $now; $DB->update_record('local_ai_bridge_role', $record);
    } else {
        $DB->insert_record('local_ai_bridge_role', (object)[
            'tenantid' => $tenantid, 'name' => $data->name, 'idnumber' => $data->idnumber,
            'isdefault' => $data->isdefault ? 1 : 0, 'enabled' => $data->enabled ? 1 : 0,
            'timecreated' => $now, 'timemodified' => $now,
        ]);
    }
    $transaction->allow_commit();
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'roles']), get_string('changessaved'));
}
echo $OUTPUT->header(); $form->display(); echo $OUTPUT->footer();
