<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

final class route_service {
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
