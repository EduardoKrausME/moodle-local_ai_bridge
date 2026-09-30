<?php
namespace local_ai_bridge\task;

defined('MOODLE_INTERNAL') || die();

final class sync_tenants extends \core\task\scheduled_task {
    public function get_name(): string {
        return get_string('task:synctenants', 'local_ai_bridge');
    }

    public function execute(): void {
        global $DB;
        if (!get_config('local_ai_bridge', 'autocreatetenants')) {
            return;
        }
        $sql = "SELECT id, institution, department
                  FROM {user}
                 WHERE deleted = 0 AND suspended = 0 AND id > 1";
        $users = $DB->get_recordset_sql($sql);
        foreach ($users as $user) {
            \local_ai_bridge\tenant_service::ensure_from_profile((string)$user->institution, (string)$user->department);
        }
        $users->close();
    }
}
