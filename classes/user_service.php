<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

final class user_service {
    public static function get_or_create(int $tenantid, int $userid): \stdClass {
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
