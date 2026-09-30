<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_login();
$tenantid = required_param('tenantid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
\local_ai_bridge\access::require_manage_tenant($tenantid);
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$record = $id ? $DB->get_record('local_ai_bridge_purpose', ['id' => $id, 'tenantid' => $tenantid], '*', MUST_EXIST) : null;
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/ai_bridge/purpose.php', ['tenantid' => $tenantid, 'id' => $id]));
$PAGE->set_title(get_string($id ? 'editpurpose' : 'addpurpose', 'local_ai_bridge'));
$PAGE->set_heading(format_string($tenant->name));
$form = new \local_ai_bridge\form\purpose_form();
$form->set_data($record ?: ['tenantid' => $tenantid, 'enabled' => 1, 'creditcost' => 1]);
if ($form->is_cancelled()) { redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'purposes'])); }
if ($data = $form->get_data()) {
    $now = time();
    if ($id) {
        foreach (['name','idnumber','systeminstruction','temperature','maxoutputtokens','creditcost'] as $field) { $record->{$field} = $data->{$field}; }
        $record->temperature = $data->temperature === '' ? null : $data->temperature;
        $record->maxoutputtokens = $data->maxoutputtokens === '' ? null : $data->maxoutputtokens;
        $record->enabled = $data->enabled ? 1 : 0; $record->timemodified = $now;
        $DB->update_record('local_ai_bridge_purpose', $record);
    } else {
        $DB->insert_record('local_ai_bridge_purpose', (object)[
            'tenantid' => $tenantid, 'name' => $data->name, 'idnumber' => $data->idnumber,
            'systeminstruction' => $data->systeminstruction, 'temperature' => $data->temperature === '' ? null : $data->temperature,
            'maxoutputtokens' => $data->maxoutputtokens === '' ? null : $data->maxoutputtokens,
            'creditcost' => $data->creditcost, 'enabled' => $data->enabled ? 1 : 0,
            'timecreated' => $now, 'timemodified' => $now,
        ]);
    }
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'purposes']), get_string('changessaved'));
}
echo $OUTPUT->header(); $form->display(); echo $OUTPUT->footer();
