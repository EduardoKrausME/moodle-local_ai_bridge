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
 * access.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

/**
 * Class access.
 */
final class access {
    /**
     * Method can_manage_tenant.
     *
     * @param int $userid Parameter userid.
     * @param int $tenantid Parameter tenantid.
     * @return bool Return value.
     */
    public static function can_manage_tenant(int $userid, int $tenantid): bool {
        global $DB;
        $context = \context_system::instance();
        if (has_capability('local/ai_bridge:manageall', $context, $userid)) {
            return true;
        }
        return $DB->record_exists('local_ai_bridge_tenant_admin', ['tenantid' => $tenantid, 'userid' => $userid]);
    }

    /**
     * Method require_manage_tenant.
     *
     * @param int $tenantid Parameter tenantid.
     * @return void Return value.
     */
    public static function require_manage_tenant(int $tenantid): void {
        global $USER;
        if (!self::can_manage_tenant((int)$USER->id, $tenantid)) {
            throw new \required_capability_exception(\context_system::instance(), 'local/ai_bridge:manageall', 'nopermissions', '');
        }
    }

    /**
     * Method has_any_tenant_admin_assignment.
     *
     * @param int $userid Parameter userid.
     * @return bool Return value.
     */
    public static function has_any_tenant_admin_assignment(int $userid): bool {
        global $DB;
        return $DB->record_exists('local_ai_bridge_tenant_admin', ['userid' => $userid]);
    }

    /**
     * Method manageable_tenants.
     *
     * @param int $userid Parameter userid.
     * @return array Return value.
     */
    public static function manageable_tenants(int $userid): array {
        global $DB;
        $context = \context_system::instance();
        if (has_capability('local/ai_bridge:manageall', $context, $userid)) {
            return $DB->get_records('local_ai_bridge_tenant', null, 'name ASC');
        }
        $sql = "SELECT t.*
                  FROM {local_ai_bridge_tenant} t
                  JOIN {local_ai_bridge_tenant_admin} a ON a.tenantid = t.id
                 WHERE a.userid = :userid
              ORDER BY t.name ASC";
        return $DB->get_records_sql($sql, ['userid' => $userid]);
    }
}
