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
 * manage.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/ai_bridge/manage.php'));
$PAGE->set_title(get_string('manage', 'local_ai_bridge'));
$PAGE->set_heading(get_string('pluginname', 'local_ai_bridge'));

$tenants = \local_ai_bridge\access::manageable_tenants((int)$USER->id);
if (!$tenants) {
    if (has_capability('local/ai_bridge:managetenants', $context, (int)$USER->id)) {
        redirect(new moodle_url('/local/ai_bridge/tenant.php'));
    }
    throw new required_capability_exception($context, 'local/ai_bridge:manageall', 'nopermissions', '');
}

$tenantid = optional_param('tenantid', 0, PARAM_INT);
if (!$tenantid) {
    $tenantid = (int)array_key_first($tenants);
}
if (!isset($tenants[$tenantid])) {
    \local_ai_bridge\access::require_manage_tenant($tenantid);
}
$tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
$tab = optional_param('tab', 'overview', PARAM_ALPHA);
$tabs = [
    new tabobject('overview', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'overview']), get_string('overview', 'local_ai_bridge')),
    new tabobject('purposes', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'purposes']), get_string('purposes', 'local_ai_bridge')),
    new tabobject('connections', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'connections']), get_string('connections', 'local_ai_bridge')),
    new tabobject('roles', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'roles']), get_string('roles', 'local_ai_bridge')),
    new tabobject('routes', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'routes']), get_string('routes', 'local_ai_bridge')),
    new tabobject('users', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'users']), get_string('users')),
    new tabobject('credits', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'credits']), get_string('credits', 'local_ai_bridge')),
    new tabobject('admins', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'admins']), get_string('tenantadmins', 'local_ai_bridge')),
    new tabobject('stats', new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $tenantid, 'tab' => 'stats']), get_string('statistics')),
];

echo $OUTPUT->header();
if (count($tenants) > 1) {
    $menu = [];
    foreach ($tenants as $item) {
        $menu[(new moodle_url('/local/ai_bridge/manage.php', ['tenantid' => $item->id, 'tab' => $tab]))->out(false)] = format_string($item->name);
    }
    echo $OUTPUT->single_select(new moodle_url('/local/ai_bridge/manage.php'), 'tenantid',
        array_map(fn($t) => format_string($t->name), $tenants), $tenantid, null, 'tenant-switch');
}
echo $OUTPUT->heading(format_string($tenant->name), 2);
if (has_capability('local/ai_bridge:managetenants', $context)) {
    echo html_writer::link(new moodle_url('/local/ai_bridge/tenant.php'), get_string('addtenant', 'local_ai_bridge'), ['class' => 'btn btn-secondary mb-3']);
}
echo $OUTPUT->tabtree($tabs, $tab);

switch ($tab) {
    case 'purposes':
        echo \local_ai_bridge\output\manager::purposes($tenant);
        break;
    case 'connections':
        echo \local_ai_bridge\output\manager::connections($tenant);
        break;
    case 'roles':
        echo \local_ai_bridge\output\manager::roles($tenant);
        break;
    case 'routes':
        echo \local_ai_bridge\output\manager::routes($tenant);
        break;
    case 'users':
        echo \local_ai_bridge\output\manager::users($tenant);
        break;
    case 'credits':
        echo \local_ai_bridge\output\manager::credits($tenant);
        break;
    case 'admins':
        echo \local_ai_bridge\output\manager::admins($tenant);
        break;
    case 'stats':
        echo \local_ai_bridge\output\manager::stats($tenant);
        break;
    default:
        echo \local_ai_bridge\output\manager::overview($tenant);
}

echo $OUTPUT->footer();
