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
 * Endpoint restrictions prevent credentials being sent to arbitrary destinations.
 */
final class url_guard_test extends advanced_testcase {
    /** @covers \\local_ai_bridge\\security\\url_guard::validate */
    public function test_hosted_providers_are_pinned(): void {
        $this->resetAfterTest();
        $this->assertNull(url_guard::validate("https://api.openai.com/v1", "openai"));
        $this->assertNull(url_guard::validate("https://api.anthropic.com/v1", "claude"));
        $this->assertNull(url_guard::validate("https://generativelanguage.googleapis.com/v1beta", "gemini"));
        $this->assertNotNull(url_guard::validate("https://evil.example.com/v1", "openai"));
        $this->assertNotNull(url_guard::validate("http://api.openai.com/v1", "openai"));
        $this->assertNotNull(url_guard::validate("https://127.0.0.1/v1", "openai"));
        $this->assertNotNull(url_guard::validate("http://localhost:11434", "ollama"));
    }

    /** @covers \\local_ai_bridge\\security\\url_guard::validate */
    public function test_private_endpoints_need_site_override(): void {
        $this->resetAfterTest();
        set_config("allowprivatehosts", 1, "local_ai_bridge");
        $this->assertNull(url_guard::validate("http://127.0.0.1:11434", "ollama"));
        $this->assertNull(url_guard::validate("http://localhost:11434", "ollama"));
        $this->assertNotNull(url_guard::validate("file:///etc/passwd", "ollama"));
        $this->assertNotNull(url_guard::validate("http://user:pass@localhost:11434", "ollama"));
    }

    /** @covers \\local_ai_bridge\\security\\url_guard::is_private_host */
    public function test_special_addresses_are_private(): void {
        foreach (["localhost", "service.local", "127.0.0.1", "10.1.2.3",
                "169.254.169.254", "192.168.1.2", "::1", "fe80::1", "::ffff:127.0.0.1"] as $host) {
            $this->assertTrue(url_guard::is_private_host($host), $host);
        }
        $this->assertFalse(url_guard::is_private_host("8.8.8.8"));
    }
}
