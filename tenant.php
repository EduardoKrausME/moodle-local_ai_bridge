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
 * tenant.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once("{$CFG->libdir}/formslib.php");

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
