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
 * privacy_provider_test.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use advanced_testcase;
use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\types\external_location;

/**
 * Verify the external data exports declared by AI bridge subplugins.
 */
final class privacy_provider_test extends advanced_testcase {
    /**
     * Verify the Privacy API declarations of each bundled bridge.
     *
     * @coversNothing
     */
    public function test_bridge_providers_declare_external_locations(): void {
        foreach (["openai", "claude", "gemini", "ollama"] as $name) {
            $component = "aibridge_" . $name;
            $provider = "\\" . $component . "\\privacy\\provider";
            $collection = new collection($component);
            $this->assertSame($collection, $provider::get_metadata($collection));
            $locations = $collection->get_collection();
            $this->assertCount(1, $locations);
            $this->assertInstanceOf(external_location::class, reset($locations));
        }
    }
}
