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
 * credit_manager_test.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use core\lock\lock_config;
use moodle_exception;
use stdClass;

use advanced_testcase;
use moodle_exception;

/**
 * Check quota reservation before spending on an external provider.
 */
final class credit_manager_test extends advanced_testcase {
    /** @covers \local_ai_bridge\credit_manager::reserve */
    public function test_quota_reservation_and_release(): void {
        global $DB;
        $this->resetAfterTest();
        $tenant = tenant_service::create([
            "name" => "Quota", "idnumber" => "quota", "enabled" => 1, "creditbalance" => 2,
        ]);
        $user = $this->getDataGenerator()->create_user();
        user_service::get_or_create((int)$tenant->id, (int)$user->id);
        credit_manager::reserve((int)$tenant->id, (int)$user->id, 1.5);
        try {
            credit_manager::reserve((int)$tenant->id, (int)$user->id, 1.5);
            $this->fail("The second reservation must be rejected.");
        } catch (moodle_exception $e) {
            $this->assertSame("error:tenantcredits", $e->errorcode);
        }
        credit_manager::release((int)$tenant->id, (int)$user->id, 1.5);
        $this->assertEqualsWithDelta(2.0, (float)$DB->get_field("local_ai_bridge_tenant",
            "creditbalance", ["id" => $tenant->id]), 0.0001);
        $this->assertEqualsWithDelta(0.0, (float)$DB->get_field("local_ai_bridge_user",
            "creditused", ["tenantid" => $tenant->id, "userid" => $user->id]), 0.0001);
    }

    /** @covers \local_ai_bridge\credit_manager::reserve */
    public function test_disabled_member_cannot_reserve(): void {
        global $DB;
        $this->resetAfterTest();
        $tenant = tenant_service::create([
            "name" => "Disabled", "idnumber" => "disabled", "enabled" => 1, "creditbalance" => 2,
        ]);
        $user = $this->getDataGenerator()->create_user();
        user_service::get_or_create((int)$tenant->id, (int)$user->id);
        $DB->set_field("local_ai_bridge_user", "enabled", 0, ["tenantid" => $tenant->id, "userid" => $user->id]);
        $this->expectException(moodle_exception::class);
        credit_manager::reserve((int)$tenant->id, (int)$user->id, 1);
    }
}
