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
 * lib.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ai_bridge\access;

/**
 * Add AI Bridge navigation for users who can manage their tenant.
 *
 * @param global_navigation $navigation
 * @throws coding_exception
 * @throws dml_exception
 */
function local_ai_bridge_extend_navigation(global_navigation $navigation): void {
    global $USER;
    if (!isloggedin() || isguestuser()) {
        return;
    }
    $context = context_system::instance();
    if (has_capability('local/ai_bridge:manageall', $context) ||
            access::has_any_tenant_admin_assignment((int)$USER->id)) {
        $navigation->add(
            get_string('pluginname', 'local_ai_bridge'),
            new moodle_url('/local/ai_bridge/manage.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_ai_bridge'
        );
    }
}
