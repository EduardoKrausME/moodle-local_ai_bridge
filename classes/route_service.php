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
 * route_service.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

/**
 * Class route_service.
 */
final class route_service {
    /**
     * Method candidates.
     *
     * @param int $tenantid Parameter tenantid.
     * @param int $purposeid Parameter purposeid.
     * @param ?int $roleid Parameter roleid.
     * @return array Return value.
     */
    public static function candidates(int $tenantid, int $purposeid, ?int $roleid): array {
        global $DB;
        $params = ['tenantid' => $tenantid, 'purposeid' => $purposeid];
        if ($roleid) {
            $rolesql = '(r.roleid = :roleid OR r.roleid IS NULL)';
            $params['roleid'] = $roleid;
        } else {
            $rolesql = 'r.roleid IS NULL';
        }
        $sql = "SELECT r.*, c.bridge, c.configdata, c.enabled AS connectionenabled
                  FROM {local_ai_bridge_route} r
                  JOIN {local_ai_bridge_connection} c ON c.id = r.connectionid
                 WHERE r.tenantid = :tenantid
                   AND r.purposeid = :purposeid
                   AND r.enabled = 1
                   AND c.enabled = 1
                   AND {$rolesql}
              ORDER BY CASE WHEN r.roleid IS NULL THEN 1 ELSE 0 END ASC, r.priority ASC, r.id ASC";
        return array_values($DB->get_records_sql($sql, $params));
    }
}
