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
 * Queue AI requests for background processing and callback delivery.
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use core\encryption;
use core\task\manager;
use invalid_parameter_exception;
use ReflectionMethod;
use local_ai_bridge\task\generate as generate_task;

/**
 * Enqueue requests that deliver their result through a static callback.
 */
class async_request {
    /**
     * Queue a request without persisting prompts in plaintext.
     *
     * @param string $purposeidnumber Purpose identifier.
     * @param array|string $messages Chat messages or simple prompt.
     * @param int $userid User responsible for the request.
     * @param array $options Provider options, which must be JSON-serializable.
     * @param callable $callback Public static callback.
     * @return string Request identifier supplied to the callback.
     */
    public static function enqueue(string $purposeidnumber, array|string $messages, int $userid,
            array $options, callable $callback): string {
        $callbackname = self::callback_name($callback);
        $requestid = bin2hex(random_bytes(16));
        $payload = json_encode([
            'purposeidnumber' => $purposeidnumber,
            'messages' => $messages,
            'userid' => $userid,
            'options' => $options,
        ], JSON_THROW_ON_ERROR);

        $task = new generate_task();
        $task->set_userid($userid);
        $task->set_custom_data([
            'requestid' => $requestid,
            'callback' => $callbackname,
            'payload' => encryption::encrypt($payload),
        ]);
        manager::queue_adhoc_task($task);
        return $requestid;
    }

    /**
     * Validate a callback that can be reconstructed in an independent cron process.
     *
     * @param callable $callback Callback in Class::method or [Class::class, 'method'] form.
     * @return string Canonical callback name.
     */
    public static function callback_name(callable $callback): string {
        if (is_string($callback)) {
            $parts = explode('::', $callback);
        } else if (is_array($callback) && count($callback) === 2 && is_string($callback[0]) &&
                is_string($callback[1])) {
            $parts = array_values($callback);
        } else {
            throw new invalid_parameter_exception('Async AI callbacks must be public static class methods.');
        }
        if (count($parts) !== 2 || !class_exists($parts[0]) || !method_exists($parts[0], $parts[1])) {
            throw new invalid_parameter_exception('Async AI callback class or method does not exist.');
        }
        $method = new ReflectionMethod($parts[0], $parts[1]);
        if (!$method->isPublic() || !$method->isStatic()) {
            throw new invalid_parameter_exception('Async AI callbacks must be public static class methods.');
        }
        return $method->getDeclaringClass()->getName() . '::' . $method->getName();
    }
}
