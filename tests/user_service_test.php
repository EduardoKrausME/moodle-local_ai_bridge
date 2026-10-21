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
 * user_service_test.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use stdClass;

use advanced_testcase;

/**
 * Tenant user eligibility checks run before any personal data is loaded.
 */
final class user_service_test extends advanced_testcase {
    /** @covers \local_ai_bridge\user_service::is_eligible_for_tenant */
    public function test_other_tenant_is_not_eligible(): void {
        $this->resetAfterTest();
        set_config("tenantkey", "institution_department", "local_ai_bridge");
        $first = tenant_service::create([
            "name" => "First", "idnumber" => "first",
            "institution" => "University A", "department" => "Math", "enabled" => 1,
        ]);
        $second = tenant_service::create([
            "name" => "Second", "idnumber" => "second",
            "institution" => "University B", "department" => "Art", "enabled" => 1,
        ]);
        $user = $this->getDataGenerator()->create_user([
            "institution" => "University B", "department" => "Art",
        ]);
        $this->assertFalse(user_service::is_eligible_for_tenant($first, (int)$user->id));
        $this->assertTrue(user_service::is_eligible_for_tenant($second, (int)$user->id));
        user_service::get_or_create((int)$first->id, (int)$user->id);
        $this->assertTrue(user_service::is_eligible_for_tenant($first, (int)$user->id));
    }

    /** @covers \local_ai_bridge\user_service::is_eligible_for_tenant */
    public function test_unmatched_profile_and_unknown_user_are_rejected(): void {
        $this->resetAfterTest();
        $tenant = tenant_service::create([
            "name" => "Tenant", "idnumber" => "tenant",
            "institution" => "University", "department" => "Science", "enabled" => 1,
        ]);
        $this->assertFalse(user_service::is_eligible_for_tenant($tenant, 999999999));
        $user = $this->getDataGenerator()->create_user([
            "institution" => "Other", "department" => "Other",
        ]);
        $this->assertFalse(user_service::is_eligible_for_tenant($tenant, (int)$user->id));
    }
}
