<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_login();
$tenantid = required_param('tenantid', PARAM_INT);
$context = context_system::instance();
require_capability('local/ai_bridge:managetenants', $context);
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$remove = optional_param('remove', 0, PARAM_INT);
if ($remove) {
    require_sesskey();
    $DB->delete_records('local_ai_bridge_tenant_admin', ['tenantid' => $tenantid, 'userid' => $remove]);
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'admins']), get_string('changessaved'));
}
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/ai_bridge/tenant_admin.php', ['tenantid' => $tenantid]));
$PAGE->set_title(get_string('addtenantadmin', 'local_ai_bridge'));
$PAGE->set_heading(format_string($tenant->name));
$form = new \local_ai_bridge\form\tenant_admin_form();
$form->set_data(['tenantid' => $tenantid]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'admins']));
}
if ($data = $form->get_data()) {
    $user = $DB->get_record('user', ['email' => core_text::strtolower(trim($data->email)), 'deleted' => 0]);
    if (!$user) {
        throw new moodle_exception('error:usernotfound', 'local_ai_bridge');
    }
    if (!$DB->record_exists('local_ai_bridge_tenant_admin', ['tenantid' => $tenantid, 'userid' => $user->id])) {
        $DB->insert_record('local_ai_bridge_tenant_admin', (object)[
            'tenantid' => $tenantid, 'userid' => $user->id, 'timecreated' => time(),
        ]);
    }
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'admins']), get_string('changessaved'));
}
echo $OUTPUT->header(); $form->display(); echo $OUTPUT->footer();
