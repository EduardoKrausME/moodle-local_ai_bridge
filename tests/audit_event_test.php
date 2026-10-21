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
 * audit_event_test.php
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
use local_ai_bridge\event\credit_adjusted;

/**
 * Check that administrative credit adjustments have a native Moodle audit record.
 */
final class audit_event_test extends advanced_testcase {
    /** @covers \local_ai_bridge\credit_manager::adjust */
    public function test_credit_adjustment_emits_event(): void {
        $this->resetAfterTest();
        $tenant = tenant_service::create([
            "name" => "Audit", "idnumber" => "audit", "enabled" => 1, "creditbalance" => 0,
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $sink = $this->redirectEvents();
        credit_manager::adjust((int)$tenant->id, 2, (int)$user->id, "Manual allocation");
        $events = $sink->get_events();
        $sink->close();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(credit_adjusted::class, $events[0]);
        $this->assertEquals($tenant->id, $events[0]->other["tenantid"]);
        $this->assertEquals(2, $events[0]->other["amount"]);
    }
}
