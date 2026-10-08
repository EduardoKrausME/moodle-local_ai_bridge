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
 * Execute an AI generation request outside the originating web request.
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\task;

use core\encryption;
use core\task\adhoc_task;
use local_ai_bridge\api;
use Throwable;

/**
 * Ad hoc task for callback-driven AI requests.
 */
class generate extends adhoc_task {
    /**
     * Generate the response and notify the original caller, including on failure.
     *
     * @return void
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        $requestid = (string)$data->requestid;
        $callback = (string)$data->callback;
        $response = null;
        $error = null;

        try {
            $payload = json_decode(encryption::decrypt((string)$data->payload), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($payload)) {
                throw new \UnexpectedValueException('Invalid async AI request payload.');
            }
            $response = api::generate(
                (string)$payload['purposeidnumber'],
                $payload['messages'],
                (int)$payload['userid'],
                (array)$payload['options']
            );
        } catch (Throwable $exception) {
            $error = $exception;
        }

        // Accounting has already finished in api::generate(). Never repeat a paid
        // provider request just because the consumer callback threw an exception.
        try {
            $callback($response, $error, $requestid);
        } catch (Throwable $exception) {
            mtrace('AI Bridge callback failure for request ' . $requestid . ' (' . get_class($exception) . ').');
        }
    }
}
