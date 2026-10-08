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
 * Tests for background callback dispatch.
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use advanced_testcase;
use core\encryption;
use core\task\manager;
use invalid_parameter_exception;

/**
 * Tests the callback contract and encrypted job payloads.
 */
final class async_request_test extends advanced_testcase {
    /**
     * A callback available in a separate PHP process.
     *
     * @param ?\local_ai_bridge\bridge\response $result AI result.
     * @param ?\Throwable $error Error, if any.
     * @param string $requestid Request identifier.
     */
    public static function completed(?\local_ai_bridge\bridge\response $result, ?\Throwable $error,
            string $requestid): void {
    }

    /**
     * Test supported callback forms.
     *
     * @covers \local_ai_bridge\async_request::callback_name
     */
    public function test_static_callback_is_accepted(): void {
        $expected = self::class . '::completed';
        $this->assertSame($expected, async_request::callback_name([self::class, 'completed']));
        $this->assertSame($expected, async_request::callback_name($expected));
    }

    /**
     * Closures cannot be executed later by the Moodle cron process.
     *
     * @covers \local_ai_bridge\async_request::callback_name
     */
    public function test_closures_are_rejected(): void {
        $this->expectException(invalid_parameter_exception::class);
        $callback = static function (): void {
        };
        async_request::callback_name($callback);
    }

    /**
     * Verify that queued prompts are encrypted instead of stored as plain task data.
     *
     * @covers \local_ai_bridge\async_request::enqueue
     */
    public function test_enqueue_encrypts_request_content(): void {
        $this->resetAfterTest();
        $prompt = 'Sensitive AI prompt for encryption test';
        $userid = (int)$this->getDataGenerator()->create_user()->id;
        $requestid = async_request::enqueue('course-assistant', $prompt, $userid, [], [self::class, 'completed']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $requestid);

        $tasks = manager::get_adhoc_tasks(\local_ai_bridge\task\generate::class);
        $this->assertCount(1, $tasks);
        $data = reset($tasks)->get_custom_data();
        $this->assertSame($requestid, $data->requestid);
        $this->assertSame(self::class . '::completed', $data->callback);
        $this->assertStringNotContainsString($prompt, json_encode($data));
        $payload = json_decode(encryption::decrypt($data->payload), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($prompt, $payload['messages']);
        $this->assertSame($userid, $payload['userid']);
    }
}
