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
 * Tests for provider endpoint validation.
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use advanced_testcase;
use local_ai_bridge\security\url_guard;

/**
 * Tests that endpoint validation does not depend on an allowed hosts setting.
 */
final class url_guard_test extends advanced_testcase {
    /**
     * Custom provider hosts and ports are accepted without a site-wide allowlist.
     *
     * @covers \local_ai_bridge\security\url_guard::validate
     */
    public function test_custom_hosts_are_accepted(): void {
        $this->assertNull(url_guard::validate('https://api.openai.com/v1'));
        $this->assertNull(url_guard::validate('https://custom-ai.example.org:8443/v1'));
        $this->assertNull(url_guard::validate('http://127.0.0.1:11434/api/chat'));
    }

    /**
     * Malformed or unsafe endpoint syntax is rejected.
     *
     * @covers \local_ai_bridge\security\url_guard::validate
     */
    public function test_invalid_endpoints_are_rejected(): void {
        foreach (['', 'file:///etc/passwd', 'ftp://example.org', 'https://', 'https://user:pass@example.org',
                'https://example.org/api#fragment'] as $url) {
            $this->assertSame(get_string('error:invalidendpoint', 'local_ai_bridge'), url_guard::validate($url));
        }
    }
}
