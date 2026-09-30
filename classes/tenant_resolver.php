<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

final class tenant_resolver {
    public static function resolve_user(int $userid): ?\stdClass {
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
