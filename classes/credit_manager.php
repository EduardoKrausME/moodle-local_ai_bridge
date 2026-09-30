<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

final class credit_manager {
    public static function assert_available(\stdClass $tenant, \stdClass $usercontrol, float $credits): void {
        if ($credits <= 0) {
            return;
        }
        if ((float)$tenant->creditbalance < $credits) {
            throw new \moodle_exception('error:tenantcredits', 'local_ai_bridge');
        }
        if ($usercontrol->creditlimit !== null && $usercontrol->creditlimit !== '' &&
                ((float)$usercontrol->creditused + $credits) > (float)$usercontrol->creditlimit) {
            throw new \moodle_exception('error:usercredits', 'local_ai_bridge');
        }
    }

    public static function debit(int $tenantid, int $userid, int $usageid, float $credits): void {
        global $DB;
        if ($credits <= 0) {
            return;
        }
        $factory = \core\lock\lock_config::get_lock_factory('local_ai_bridge');
        $lock = $factory->get_lock('credits_tenant_' . $tenantid, 10);
        if (!$lock) {
            throw new \moodle_exception('error:creditlock', 'local_ai_bridge');
        }
        try {
            $tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
            $user = user_service::get_or_create($tenantid, $userid);
            self::assert_available($tenant, $user, $credits);
            $tenant->creditbalance = (float)$tenant->creditbalance - $credits;
            $tenant->timemodified = time();
            $DB->update_record('local_ai_bridge_tenant', $tenant);
            $user->creditused = (float)$user->creditused + $credits;
            $user->timemodified = time();
            $DB->update_record('local_ai_bridge_user', $user);
            $DB->insert_record('local_ai_bridge_credit', (object)[
                'tenantid' => $tenantid,
                'userid' => $userid,
                'actorid' => null,
                'usageid' => $usageid,
                'amount' => -$credits,
                'type' => 'usage',
                'note' => null,
                'timecreated' => time(),
            ]);
        } finally {
            $lock->release();
        }
    }

    public static function adjust(int $tenantid, float $amount, int $actorid, string $note = ''): void {
        global $DB;
        $factory = \core\lock\lock_config::get_lock_factory('local_ai_bridge');
        $lock = $factory->get_lock('credits_tenant_' . $tenantid, 10);
        if (!$lock) {
            throw new \moodle_exception('error:creditlock', 'local_ai_bridge');
        }
        try {
            $transaction = $DB->start_delegated_transaction();
            $tenant = $DB->get_record('local_ai_bridge_tenant', ['id' => $tenantid], '*', MUST_EXIST);
            $tenant->creditbalance = (float)$tenant->creditbalance + $amount;
            $tenant->timemodified = time();
            $DB->update_record('local_ai_bridge_tenant', $tenant);
            $DB->insert_record('local_ai_bridge_credit', (object)[
                'tenantid' => $tenantid,
                'userid' => null,
                'actorid' => $actorid,
                'usageid' => null,
                'amount' => $amount,
                'type' => $amount >= 0 ? 'purchase' : 'adjustment',
                'note' => $note,
                'timecreated' => time(),
            ]);
            $transaction->allow_commit();
        } finally {
            $lock->release();
        }
    }

}
