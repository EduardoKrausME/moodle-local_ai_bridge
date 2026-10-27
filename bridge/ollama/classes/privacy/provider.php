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
 * @package   aibridge_ollama
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aibridge_ollama\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadata_provider;
use context;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\plugin\provider as request_provider;
use core_privacy\local\request\userlist;

/**
 * Class provider.
 */
class provider implements metadata_provider, request_provider, core_userlist_provider {
    /**
     * Describe the personal data sent to this external AI destination.
     *
     * @param collection $collection Metadata collection.
     * @return collection Updated metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link("ollama", [
            "messages" => "privacy:metadata:external:messages",
            "systeminstruction" => "privacy:metadata:external:systeminstruction",
        ], "privacy:metadata:external");
        return $collection;
    }

    /**
     * No user data is stored by this bridge. The parent plugin owns usage data.
     *
     * @param int $userid User ID.
     * @return contextlist Empty list of contexts.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    /**
     * The bridge has no stored personal data to export.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        // Usage records are exported by local_ai_bridge.
    }

    /**
     * The bridge has no locally stored personal data to delete.
     *
     * @param context $context Context whose data is being deleted.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        // Usage records are deleted by local_ai_bridge.
    }

    /**
     * The bridge has no locally stored personal data to delete.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        // Usage records are deleted by local_ai_bridge.
    }

    /**
     * The bridge does not own user records in any context.
     *
     * @param userlist $userlist User list to populate.
     */
    public static function get_users_in_context(userlist $userlist): void {
        // The parent plugin lists users from its own tables.
    }

    /**
     * The bridge has no locally stored personal data to delete.
     *
     * @param approved_userlist $userlist Approved users.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        // Usage records are deleted by local_ai_bridge.
    }
}
