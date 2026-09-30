<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

final class access {
    public static function can_manage_tenant(int $userid, int $tenantid): bool {
        global $DB;
        $context = \context_system::instance();
        if (has_capability('local/ai_bridge:manageall', $context, $userid)) {
            return true;
        }
        return $DB->record_exists('local_ai_bridge_tenant_admin', ['tenantid' => $tenantid, 'userid' => $userid]);
    }

    public static function require_manage_tenant(int $tenantid): void {
        global $USER;
        if (!self::can_manage_tenant((int)$USER->id, $tenantid)) {
            throw new \required_capability_exception(\context_system::instance(), 'local/ai_bridge:manageall', 'nopermissions', '');
        }
    }

    public static function has_any_tenant_admin_assignment(int $userid): bool {
        global $DB;
        return $DB->record_exists('local_ai_bridge_tenant_admin', ['userid' => $userid]);
    }

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
