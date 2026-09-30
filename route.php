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
 * route.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ai_bridge\access;
use local_ai_bridge\form\route_form;

require_once(__DIR__ . '/../../config.php');
require_once("{$CFG->libdir}/formslib.php");
require_login();
$tenantid = required_param('tenantid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
access::require_manage_tenant($tenantid);
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$record = $id ? $DB->get_record('local_ai_bridge_route', ['id' => $id, 'tenantid' => $tenantid], '*', MUST_EXIST) : null;
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/ai_bridge/route.php', ['tenantid' => $tenantid, 'id' => $id]));
$PAGE->set_title(get_string($id ? 'editroute' : 'addroute', 'local_ai_bridge'));
$PAGE->set_heading(format_string($tenant->name));
$form = new route_form(null, ['tenantid' => $tenantid]);
if ($record) {
    $form->set_data($record);
}
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'routes']));
}
if ($data = $form->get_data()) {
    $now = time();
    if ($record) {
        $record->purposeid = $data->purposeid;
        $record->roleid = empty($data->roleid) ? null : $data->roleid;
        $record->connectionid = $data->connectionid;
        $record->model = $data->model;
        $record->priority = $data->priority;
        $record->enabled = $data->enabled ? 1 : 0;
        $record->timemodified = $now;
        $DB->update_record('local_ai_bridge_route', $record);
    } else {
        $DB->insert_record('local_ai_bridge_route', (object)[
            'tenantid' => $tenantid, 'purposeid' => $data->purposeid, 'roleid' => empty($data->roleid) ? null : $data->roleid,
            'connectionid' => $data->connectionid, 'model' => $data->model, 'priority' => $data->priority,
            'enabled' => $data->enabled ? 1 : 0, 'timecreated' => $now, 'timemodified' => $now,
        ]);
    }
    redirect(new moodle_url('/local/ai_bridge/manage.php',
        ['tenantid' => $tenantid, 'tab' => 'routes']), get_string('changessaved'));
}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
