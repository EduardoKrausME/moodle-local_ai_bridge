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
 * credit_manager.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use core\lock\lock_config;
use moodle_exception;
use stdClass;

/**
 * Class credit_manager.
 */
class credit_manager {
    /**
     * Method assert_available.
     *
     * @param stdClass $tenant Parameter tenant.
     * @param stdClass $usercontrol Parameter usercontrol.
     * @param float $credits Parameter credits.
     * @return void Return value.
     */
    public static function assert_available(stdClass $tenant, stdClass $usercontrol, float $credits): void {
        if ($credits <= 0) {
            return;
        }
        if ((float)$tenant->creditbalance < $credits) {
            throw new moodle_exception('error:tenantcredits', 'local_ai_bridge');
        }
        if ($usercontrol->creditlimit !== null && $usercontrol->creditlimit !== '' &&
            ((float)$usercontrol->creditused + $credits) > (float)$usercontrol->creditlimit) {
            throw new moodle_exception('error:usercredits', 'local_ai_bridge');
        }
    }

    /**
     * Method debit.
     *
     * @param int $tenantid Parameter tenantid.
     * @param int $userid Parameter userid.
     * @param int $usageid Parameter usageid.
     * @param float $credits Parameter credits.
     * @return void Return value.
     */
    public static function debit(int $tenantid, int $userid, int $usageid, float $credits): void {
        global $DB;
        if ($credits <= 0) {
            return;
        }
        $factory = lock_config::get_lock_factory('local_ai_bridge');
        $lock = $factory->get_lock('credits_tenant_' . $tenantid, 10);
        if (!$lock) {
            throw new moodle_exception('error:creditlock', 'local_ai_bridge');
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

    /**
     * Method adjust.
     *
     * @param int $tenantid Parameter tenantid.
     * @param float $amount Parameter amount.
     * @param int $actorid Parameter actorid.
     * @param string $note Parameter note.
     * @return void Return value.
     */
    public static function adjust(int $tenantid, float $amount, int $actorid, string $note = ''): void {
        global $DB;
        $factory = lock_config::get_lock_factory('local_ai_bridge');
        $lock = $factory->get_lock('credits_tenant_' . $tenantid, 10);
        if (!$lock) {
            throw new moodle_exception('error:creditlock', 'local_ai_bridge');
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
