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
 * credits.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ai_bridge\access;
use local_ai_bridge\credit_manager;
use local_ai_bridge\form\credit_form;

require_once(__DIR__ . '/../../config.php');
require_once("{$CFG->libdir}/formslib.php");

require_login();
$tenantid = required_param('tenantid', PARAM_INT);
access::require_manage_tenant($tenantid);
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/ai_bridge/credits.php', ['tenantid' => $tenantid]));
$PAGE->set_title(get_string('adjustcredits', 'local_ai_bridge'));
$PAGE->set_heading(format_string($tenant->name));
$form = new credit_form();
$form->set_data(['tenantid' => $tenantid]);
if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'credits']));
}
if ($data = $form->get_data()) {
    credit_manager::adjust($tenantid, (float)$data->amount, (int)$USER->id, (string)$data->note);
    redirect(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'credits']),
        get_string('changessaved'));
}
echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
