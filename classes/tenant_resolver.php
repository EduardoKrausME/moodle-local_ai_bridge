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
final class tenant_resolver {
    /**
     * Method resolve_user.
     *
     * @param int $userid Parameter userid.
     * @return ?stdClass Return value.
     */
    public static function resolve_user(int $userid): ?stdClass {
        global $DB;
        $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], 'id,institution,department', MUST_EXIST);
        $mode = get_config('local_ai_bridge', 'tenantkey') ?: 'institution_department';
        $conditions = tenant_service::profile_conditions($mode, (string)$user->institution, (string)$user->department);
        if ($conditions === null) {
            return null;
        }
        $conditions['enabled'] = 1;
        $matches = $DB->get_records('local_ai_bridge_tenant', $conditions, 'id ASC', '*', 0, 1);
        if ($matches) {
            return reset($matches);
        }
        return tenant_service::ensure_from_profile((string)$user->institution, (string)$user->department);
    }
}
