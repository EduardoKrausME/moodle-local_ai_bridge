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
 * user_service.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use stdClass;

/**
 * Class user_service.
 */
final class user_service {
    /**
     * Method get_or_create.
     *
     * @param int $tenantid Parameter tenantid.
     * @param int $userid Parameter userid.
     * @return stdClass Return value.
     */
    public static function get_or_create(int $tenantid, int $userid): stdClass {
        global $DB;
        $record = $DB->get_record('local_ai_bridge_user', ['tenantid' => $tenantid, 'userid' => $userid]);
        if ($record) {
            return $record;
        }
        $role = $DB->get_record('local_ai_bridge_role', ['tenantid' => $tenantid, 'isdefault' => 1, 'enabled' => 1]);
        $now = time();
        $record = (object)[
            'tenantid' => $tenantid,
            'userid' => $userid,
            'roleid' => $role ? $role->id : null,
            'enabled' => 1,
            'creditlimit' => null,
            'creditused' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $record->id = $DB->insert_record('local_ai_bridge_user', $record);
        return $record;
    }
}
