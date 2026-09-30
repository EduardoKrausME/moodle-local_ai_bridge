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
 * user.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_login();
$tenantid = required_param('tenantid', PARAM_INT);
$userid = required_param('userid', PARAM_INT);
\local_ai_bridge\access::require_manage_tenant($tenantid);
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], '*', MUST_EXIST);
$control = \local_ai_bridge\user_service::get_or_create($tenantid, $userid);
$roles = $DB->get_records_menu('local_ai_bridge_role', ['tenantid' => $tenantid, 'enabled' => 1], 'name ASC', 'id,name');
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/ai_bridge/user.php', ['tenantid' => $tenantid, 'userid' => $userid]));
$PAGE->set_title(get_string('editusercontrol', 'local_ai_bridge'));
$PAGE->set_heading(format_string($tenant->name));
$form = new \local_ai_bridge\form\user_form(null, ['roles' => $roles, 'user' => $user]);
$form->set_data($control);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'users']));
}
if ($data = $form->get_data()) {
    $control->roleid = $data->roleid ?: null;
    $control->enabled = $data->enabled ? 1 : 0;
    $control->creditlimit = $data->creditlimit === '' ? null : (float)$data->creditlimit;
    $control->timemodified = time();
    $DB->update_record('local_ai_bridge_user', $control);
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'users']), get_string('changessaved'));
}
echo $OUTPUT->header(); $form->display(); echo $OUTPUT->footer();
