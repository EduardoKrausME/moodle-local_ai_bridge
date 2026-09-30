<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

require_login();
$tenantid = required_param('tenantid', PARAM_INT);
\local_ai_bridge\access::require_manage_tenant($tenantid);
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/ai_bridge/credits.php', ['tenantid' => $tenantid]));
$PAGE->set_title(get_string('adjustcredits', 'local_ai_bridge'));
$PAGE->set_heading(format_string($tenant->name));
$form = new \local_ai_bridge\form\credit_form();
$form->set_data(['tenantid' => $tenantid]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'credits']));
}
if ($data = $form->get_data()) {
    \local_ai_bridge\credit_manager::adjust($tenantid, (float)$data->amount, (int)$USER->id, (string)$data->note);
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'credits']), get_string('changessaved'));
}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
