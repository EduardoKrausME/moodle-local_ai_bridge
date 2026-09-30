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
 * tenant_service.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use stdClass;

/**
 * Class tenant_service.
 */
class tenant_service {
    /**
     * Method create.
     *
     * @param array $data Parameter data.
     * @return stdClass Return value.
     */
    public static function create(array $data): stdClass {
        global $DB;
        $now = time();
        $record = (object)[
            'name' => trim((string)$data['name']),
            'idnumber' => trim((string)$data['idnumber']),
            'institution' => trim((string)($data['institution'] ?? '')),
            'department' => trim((string)($data['department'] ?? '')),
            'enabled' => empty($data['enabled']) ? 0 : 1,
            'creditbalance' => (float)($data['creditbalance'] ?? 0),
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $transaction = $DB->start_delegated_transaction();
        $record->id = $DB->insert_record('local_ai_bridge_tenant', $record);
        self::create_default_roles((int)$record->id);
        $transaction->allow_commit();
        return $record;
    }

    /**
     * Method create_default_roles.
     *
     * @param int $tenantid Parameter tenantid.
     * @return void Return value.
     */
    public static function create_default_roles(int $tenantid): void {
        global $DB;
        $now = time();
        foreach ([['Student', 'student', 1], ['Teacher', 'teacher', 0]] as [$name, $idnumber, $isdefault]) {
            if (!$DB->record_exists('local_ai_bridge_role', ['tenantid' => $tenantid, 'idnumber' => $idnumber])) {
                $DB->insert_record('local_ai_bridge_role', (object)[
                    'tenantid' => $tenantid,
                    'name' => $name,
                    'idnumber' => $idnumber,
                    'isdefault' => $isdefault,
                    'enabled' => 1,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ]);
            }
        }
    }

    /**
     * Method ensure_from_profile.
     *
     * @param string $institution Parameter institution.
     * @param string $department Parameter department.
     * @return ?stdClass Return value.
     */
    public static function ensure_from_profile(string $institution, string $department): ?stdClass {
        global $DB;
        $mode = get_config('local_ai_bridge', 'tenantkey') ?: 'institution_department';
        $conditions = self::profile_conditions($mode, $institution, $department);
        if ($conditions === null) {
            return null;
        }
        $matches = $DB->get_records('local_ai_bridge_tenant', $conditions, 'id ASC', '*', 0, 1);
        if ($matches) {
            return reset($matches);
        }
        if (!get_config('local_ai_bridge', 'autocreatetenants')) {
            return null;
        }
        $parts = array_filter([$institution, $department], static fn($value) => trim((string)$value) !== '');
        $name = implode(' / ', $parts);
        $idbase = clean_param(core_text::strtolower($name), PARAM_ALPHANUMEXT);
        $idbase = trim(str_replace(' ', '-', $idbase), '-');
        if ($idbase === '') {
            $idbase = 'tenant';
        }
        $idnumber = $idbase;
        $suffix = 2;
        while ($DB->record_exists('local_ai_bridge_tenant', ['idnumber' => $idnumber])) {
            $idnumber = $idbase . '-' . $suffix++;
        }
        return self::create([
            'name' => $name ?: $idnumber,
            'idnumber' => $idnumber,
            'institution' => $institution,
            'department' => $department,
            'enabled' => 1,
        ]);
    }

    /**
     * Method profile_conditions.
     *
     * @param string $mode Parameter mode.
     * @param string $institution Parameter institution.
     * @param string $department Parameter department.
     * @return ?array Return value.
     */
    public static function profile_conditions(string $mode, string $institution, string $department): ?array {
        $institution = trim($institution);
        $department = trim($department);
        if ($mode === 'institution') {
            return $institution === '' ? null : ['institution' => $institution];
        }
        if ($mode === 'department') {
            return $department === '' ? null : ['department' => $department];
        }
        if ($institution === '' || $department === '') {
            return null;
        }
        return ['institution' => $institution, 'department' => $department];
    }
}
