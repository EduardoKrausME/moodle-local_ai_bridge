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
 * tenant_resolver.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use stdClass;

/**
 * Class tenant_resolver.
 */
class tenant_resolver {
    /**
     * Method resolve_user.
     *
     * @param int $userid Parameter userid.
     * @return ?stdClass Return value.
     */
    public static function resolve_user(int $userid): ?stdClass {
        global $DB;

        // Only an existing administrative assignment authorizes tenant access.
        if (!$DB->record_exists("user", ["id" => $userid, "deleted" => 0, "suspended" => 0])) {
            return null;
        }
        $sql = "SELECT t.*
                  FROM {local_ai_bridge_tenant} t
                  JOIN {local_ai_bridge_user} u ON u.tenantid = t.id
                 WHERE u.userid = :userid AND t.enabled = 1
              ORDER BY t.id ASC";
        $matches = $DB->get_records_sql($sql, ["userid" => $userid], 0, 2);

        // Without a tenant selector, multiple memberships are ambiguous and must fail closed.
        return count($matches) === 1 ? reset($matches) : null;
    }
}
