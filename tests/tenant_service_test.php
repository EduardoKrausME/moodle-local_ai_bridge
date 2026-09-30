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
 * tenant_service_test.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

/**
 * Class tenant_service_test.
 */
final class tenant_service_test extends \advanced_testcase {
    /**
     * Method test_profile_conditions.
     *
     * @return void Return value.
     */
    public function test_profile_conditions(): void {
        $this->assertSame(['institution' => 'University'],
            tenant_service::profile_conditions('institution', 'University', 'Math'));
        $this->assertSame(['department' => 'Math'],
            tenant_service::profile_conditions('department', 'University', 'Math'));
        $this->assertSame(['institution' => 'University', 'department' => 'Math'],
            tenant_service::profile_conditions('institution_department', 'University', 'Math'));
        $this->assertNull(
            tenant_service::profile_conditions('institution_department', 'University', ''));
    }
}
