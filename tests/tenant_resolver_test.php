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
 * tenant_resolver_test.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use advanced_testcase;

/**
 * Test explicit AI tenant assignments.
 */
final class tenant_resolver_test extends advanced_testcase {
    /**
     * Editing profile values must not create or select tenant membership.
     *
     * @covers \local_ai_bridge\tenant_resolver::resolve_user
     */
    public function test_spoofed_profile_does_not_authorize(): void {
        global $DB;

        $this->resetAfterTest();
        set_config("tenantkey", "institution_department", "local_ai_bridge");
        set_config("autocreatetenants", 1, "local_ai_bridge");
        $tenant = tenant_service::create([
            "name" => "Target", "idnumber" => "target",
            "institution" => "Target", "department" => "Finance", "enabled" => 1,
        ]);
        $user = $this->getDataGenerator()->create_user(["institution" => "Other", "department" => "Other"]);
        $this->assertNull(tenant_resolver::resolve_user((int)$user->id));

        $DB->set_field("user", "institution", "Target", ["id" => $user->id]);
        $DB->set_field("user", "department", "Finance", ["id" => $user->id]);
        $this->assertNull(tenant_resolver::resolve_user((int)$user->id));
        $this->assertFalse($DB->record_exists("local_ai_bridge_user", ["userid" => $user->id]));

        user_service::get_or_create((int)$tenant->id, (int)$user->id);
        $this->assertEquals($tenant->id, tenant_resolver::resolve_user((int)$user->id)->id);
        $DB->set_field("user", "institution", "Changed", ["id" => $user->id]);
        $this->assertEquals($tenant->id, tenant_resolver::resolve_user((int)$user->id)->id);
    }

    /**
     * Two valid tenant assignments cannot be selected implicitly.
     *
     * @covers \local_ai_bridge\tenant_resolver::resolve_user
     */
    public function test_multiple_tenants_fail_closed(): void {
        global $DB;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $first = tenant_service::create(["name" => "First", "idnumber" => "first", "enabled" => 1]);
        $second = tenant_service::create(["name" => "Second", "idnumber" => "second", "enabled" => 1]);
        user_service::get_or_create((int)$first->id, (int)$user->id);
        user_service::get_or_create((int)$second->id, (int)$user->id);
        $this->assertNull(tenant_resolver::resolve_user((int)$user->id));

        $DB->set_field("local_ai_bridge_tenant", "enabled", 0, ["id" => $second->id]);
        $this->assertEquals($first->id, tenant_resolver::resolve_user((int)$user->id)->id);
        $DB->set_field("user", "suspended", 1, ["id" => $user->id]);
        $this->assertNull(tenant_resolver::resolve_user((int)$user->id));
    }
}
