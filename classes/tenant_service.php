<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

final class tenant_service {
    public static function create(array $data): \stdClass {
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

    public static function ensure_from_profile(string $institution, string $department): ?\stdClass {
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
