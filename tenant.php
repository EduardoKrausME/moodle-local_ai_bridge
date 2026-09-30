<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

require_login();
require_capability('local/ai_bridge:managetenants', context_system::instance());
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/ai_bridge/tenant.php'));
$PAGE->set_title(get_string('addtenant', 'local_ai_bridge'));
$PAGE->set_heading(get_string('pluginname', 'local_ai_bridge'));
$form = new \local_ai_bridge\form\tenant_form();
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/ai_bridge/manage.php'));
}
if ($data = $form->get_data()) {
    $tenant = \local_ai_bridge\tenant_service::create((array)$data);
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenant->id]), get_string('changessaved'));
}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
