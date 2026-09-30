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
 * sync_tenants.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\task;

use core\task\scheduled_task;
use local_ai_bridge\tenant_service;

/**
 * Class sync_tenants.
 */
class sync_tenants extends scheduled_task {
    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('task:synctenants', 'local_ai_bridge');
    }

    /**
     * Method execute.
     *
     * @return void Return value.
     */
    public function execute(): void {
        global $DB;
        if (!get_config('local_ai_bridge', 'autocreatetenants')) {
            return;
        }
        $sql = "SELECT id, institution, department
                  FROM {user}
                 WHERE deleted = 0 AND suspended = 0 AND id > 1";
        $users = $DB->get_recordset_sql($sql);
        foreach ($users as $user) {
            tenant_service::ensure_from_profile((string)$user->institution, (string)$user->department);
        }
        $users->close();
    }
}
