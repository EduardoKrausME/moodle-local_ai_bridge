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
 * provider.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\privacy;

use context;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
final class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    core_userlist_provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_ai_bridge_user', [
            'userid' => 'privacy:metadata:user:userid',
            'roleid' => 'privacy:metadata:user:roleid',
            'enabled' => 'privacy:metadata:user:enabled',
            'creditlimit' => 'privacy:metadata:user:creditlimit',
            'creditused' => 'privacy:metadata:user:creditused',
        ], 'privacy:metadata:user');
        $collection->add_database_table('local_ai_bridge_usage', [
            'userid' => 'privacy:metadata:usage:userid',
            'bridge' => 'privacy:metadata:usage:bridge',
            'model' => 'privacy:metadata:usage:model',
            'inputtokens' => 'privacy:metadata:usage:inputtokens',
            'outputtokens' => 'privacy:metadata:usage:outputtokens',
            'credits' => 'privacy:metadata:usage:credits',
            'timecreated' => 'privacy:metadata:usage:timecreated',
        ], 'privacy:metadata:usage');
        $collection->add_database_table('local_ai_bridge_tenant_admin', [
            'userid' => 'privacy:metadata:admin:userid',
        ], 'privacy:metadata:admin');
        $collection->add_database_table('local_ai_bridge_credit', [
            'userid' => 'privacy:metadata:credit:userid',
            'actorid' => 'privacy:metadata:credit:actorid',
            'amount' => 'privacy:metadata:credit:amount',
            'type' => 'privacy:metadata:credit:type',
            'note' => 'privacy:metadata:credit:note',
            'timecreated' => 'privacy:metadata:credit:timecreated',
        ], 'privacy:metadata:credit');
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();
        $hasdata = $DB->record_exists('local_ai_bridge_user', ['userid' => $userid]) ||
            $DB->record_exists('local_ai_bridge_usage', ['userid' => $userid]) ||
            $DB->record_exists('local_ai_bridge_tenant_admin', ['userid' => $userid]) ||
            $DB->record_exists('local_ai_bridge_credit', ['userid' => $userid]) ||
            $DB->record_exists('local_ai_bridge_credit', ['actorid' => $userid]);
        if ($hasdata) {
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $context = context_system::instance();
        if (!in_array($context->id, $contextlist->get_contextids(), true)) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        $data = (object)[
            'controls' => array_values($DB->get_records('local_ai_bridge_user', ['userid' => $userid])),
            'usage' => array_values($DB->get_records('local_ai_bridge_usage', ['userid' => $userid], 'timecreated ASC')),
            'tenantadmin' => array_values($DB->get_records('local_ai_bridge_tenant_admin', ['userid' => $userid])),
            'credits_as_user' => array_values($DB->get_records('local_ai_bridge_credit', ['userid' => $userid], 'timecreated ASC')),
            'credits_as_actor' => array_values($DB->get_records('local_ai_bridge_credit', ['actorid' => $userid], 'timecreated ASC')),
        ];
        writer::with_context($context)->export_data([
            get_string('pluginname', 'local_ai_bridge')
        ], $data);
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if (!$context instanceof context_system) {
            return;
        }
        $DB->delete_records('local_ai_bridge_usage');
        $DB->delete_records('local_ai_bridge_user');
        $DB->delete_records('local_ai_bridge_tenant_admin');
        $DB->set_field('local_ai_bridge_credit', 'userid', null);
        $DB->set_field('local_ai_bridge_credit', 'actorid', null);
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $context = context_system::instance();
        if (!in_array($context->id, $contextlist->get_contextids(), true)) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        self::delete_userid($userid);
    }

    /**
     * Method get_users_in_context.
     *
     * @param userlist $userlist Parameter userlist.
     * @return void Return value.
     */
    public static function get_users_in_context(userlist $userlist): void {
        if (!$userlist->get_context() instanceof context_system) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ai_bridge_user}', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ai_bridge_usage}', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ai_bridge_tenant_admin}', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ai_bridge_credit} WHERE userid IS NOT NULL', []);
        $userlist->add_from_sql('userid', 'SELECT actorid AS userid FROM {local_ai_bridge_credit} WHERE actorid IS NOT NULL', []);
    }

    /**
     * Method delete_data_for_users.
     *
     * @param approved_userlist $userlist Parameter userlist.
     * @return void Return value.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        if (!$userlist->get_context() instanceof context_system) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            self::delete_userid((int)$userid);
        }
    }

    /**
     * Method delete_userid.
     *
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    private static function delete_userid(int $userid): void {
        global $DB;
        $DB->delete_records('local_ai_bridge_tenant_admin', ['userid' => $userid]);
        $DB->delete_records('local_ai_bridge_user', ['userid' => $userid]);
        $DB->delete_records('local_ai_bridge_usage', ['userid' => $userid]);
        $DB->set_field('local_ai_bridge_credit', 'userid', null, ['userid' => $userid]);
        $DB->set_field('local_ai_bridge_credit', 'actorid', null, ['actorid' => $userid]);
    }
}
