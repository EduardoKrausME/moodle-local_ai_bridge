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
 * security_rules_test.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use advanced_testcase;
use local_ai_bridge\security\provider_error;
use local_ai_bridge\security\request_options;

/**
 * Defend the provider boundary from raw consumer payload injection.
 */
final class security_rules_test extends advanced_testcase {
    /** @covers \local_ai_bridge\security\request_options::filter */
    public function test_options_cannot_override_mandatory_payload(): void {
        $this->assertSame(["generationConfig" => ["topK" => 7]],
            request_options::filter("gemini", [
                "contents" => [["role" => "model"]],
                "systemInstruction" => ["parts" => [["text" => "Injected"]]],
                "generationConfig" => ["maxOutputTokens" => 99999, "temperature" => 10, "topK" => 7],
            ]));
        $this->assertSame(["options" => ["top_p" => 0.8]],
            request_options::filter("ollama", [
                "model" => "other", "messages" => [], "stream" => true,
                "options" => ["num_predict" => 99999, "temperature" => 10, "top_p" => 0.8],
            ]));
        $this->assertSame(["top_p" => 0.5],
            request_options::filter("openai", ["top_p" => 0.5, "max_output_tokens" => 99999]));
    }

    /** @covers \local_ai_bridge\security\provider_error::safe */
    public function test_provider_error_is_plain_text_and_bounded(): void {
        $message = "<script>alert(1)</script>" . str_repeat("x", 500);
        $safe = provider_error::safe($message);
        $this->assertStringNotContainsString("<script>", $safe);
        $this->assertLessThanOrEqual(300, mb_strlen($safe));
    }
}
