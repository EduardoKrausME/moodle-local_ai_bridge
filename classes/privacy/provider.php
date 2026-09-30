<?php
namespace local_ai_bridge\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

final class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

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

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $context = \context_system::instance();
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
        \core_privacy\local\request\writer::with_context($context)->export_data([
            get_string('pluginname', 'local_ai_bridge')
        ], $data);
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if (!$context instanceof \context_system) {
            return;
        }
        $DB->delete_records('local_ai_bridge_usage');
        $DB->delete_records('local_ai_bridge_user');
        $DB->delete_records('local_ai_bridge_tenant_admin');
        $DB->set_field('local_ai_bridge_credit', 'userid', null);
        $DB->set_field('local_ai_bridge_credit', 'actorid', null);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $context = \context_system::instance();
        if (!in_array($context->id, $contextlist->get_contextids(), true)) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        self::delete_userid($userid);
    }

    public static function get_users_in_context(userlist $userlist): void {
        if (!$userlist->get_context() instanceof \context_system) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ai_bridge_user}', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ai_bridge_usage}', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ai_bridge_tenant_admin}', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ai_bridge_credit} WHERE userid IS NOT NULL', []);
        $userlist->add_from_sql('userid', 'SELECT actorid AS userid FROM {local_ai_bridge_credit} WHERE actorid IS NOT NULL', []);
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        if (!$userlist->get_context() instanceof \context_system) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            self::delete_userid((int)$userid);
        }
    }

    private static function delete_userid(int $userid): void {
        global $DB;
        $DB->delete_records('local_ai_bridge_tenant_admin', ['userid' => $userid]);
        $DB->delete_records('local_ai_bridge_user', ['userid' => $userid]);
        $DB->delete_records('local_ai_bridge_usage', ['userid' => $userid]);
        $DB->set_field('local_ai_bridge_credit', 'userid', null, ['userid' => $userid]);
        $DB->set_field('local_ai_bridge_credit', 'actorid', null, ['actorid' => $userid]);
    }
}
