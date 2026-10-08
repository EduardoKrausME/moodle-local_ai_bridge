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
 * api.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use context_system;
use invalid_parameter_exception;
use local_ai_bridge\bridge\request;
use local_ai_bridge\bridge\response;
use moodle_exception;
use required_capability_exception;
use Throwable;

/**
 * Class api.
 */
class api {
    /**
     * Generate text through the configured bridge for a tenant, purpose and logical role.
     *
     * @param string $purposeidnumber Purpose identifier configured for the tenant.
     * @param array|string $messages Either a prompt string or normalized chat messages.
     * @param int|null $userid Moodle user id, defaults to current user.
     * @param array $options Optional request options passed through to the provider.
     * @param callable|null $callback Optional public static callback for asynchronous requests.
     * @return response|string Synchronous response or async request id when a callback is provided.
     */
    public static function generate(string $purposeidnumber, array|string $messages, ?int $userid = null,
                                    array $options = [], ?callable $callback = null): response|string {
        global $DB, $USER;
        $userid ??= (int)$USER->id;
        $context = context_system::instance();
        if (!has_capability('local/ai_bridge:use', $context, $userid)) {
            throw new required_capability_exception($context, 'local/ai_bridge:use', 'nopermissions', '');
        }
        if ($callback !== null) {
            return async_request::enqueue($purposeidnumber, $messages, $userid, $options, $callback);
        }
        $tenant = tenant_resolver::resolve_user($userid);
        if (!$tenant || !$tenant->enabled) {
            throw new moodle_exception('error:notenant', 'local_ai_bridge');
        }
        $usercontrol = user_service::get_or_create((int)$tenant->id, $userid);
        if (!$usercontrol->enabled) {
            throw new moodle_exception('error:userdisabled', 'local_ai_bridge');
        }
        $purpose = $DB->get_record('local_ai_bridge_purpose', [
            'tenantid' => $tenant->id,
            'idnumber' => $purposeidnumber,
            'enabled' => 1,
        ]);
        if (!$purpose) {
            throw new moodle_exception('error:purposeunavailable', 'local_ai_bridge', '', $purposeidnumber);
        }
        credit_manager::assert_available($tenant, $usercontrol, (float)$purpose->creditcost);
        $routes = route_service::candidates((int)$tenant->id, (int)$purpose->id,
            $usercontrol->roleid ? (int)$usercontrol->roleid : null);
        if (!$routes) {
            throw new moodle_exception('error:noroute', 'local_ai_bridge');
        }
        if (is_string($messages)) {
            $messages = [['role' => 'user', 'content' => $messages]];
        }
        $request = new request(
            self::normalize_messages($messages),
            $purpose->temperature === null ? null : (float)$purpose->temperature,
            $purpose->maxoutputtokens === null ? null : (int)$purpose->maxoutputtokens,
            $options
        );
        $request = $request->with_system_instruction((string)$purpose->systeminstruction);
        $last = null;
        foreach ($routes as $route) {
            $started = hrtime(true);
            try {
                $bridge = bridge_manager::get_bridge((string)$route->bridge);
                $config = bridge_manager::decrypt_config((string)$route->configdata);
                $response = $bridge->generate($request, $config, (string)$route->model);
            } catch (Throwable $e) {
                $latency = (int)round((hrtime(true) - $started) / 1_000_000);
                usage_logger::failure($tenant, $userid, $purpose, $usercontrol, $route, $e, $latency);
                $last = $e;
                continue;
            }

            // Never retry a provider after it returned successfully. If accounting fails,
            // propagate the accounting error instead of issuing a duplicate paid request.
            $latency = (int)round((hrtime(true) - $started) / 1_000_000);
            $transaction = $DB->start_delegated_transaction();
            $usageid = usage_logger::success($tenant, $userid, $purpose, $usercontrol, $route, $response, $latency);
            credit_manager::debit((int)$tenant->id, $userid, $usageid, (float)$purpose->creditcost);
            $transaction->allow_commit();
            return $response;
        }
        if ($last instanceof moodle_exception) {
            throw $last;
        }
        throw new moodle_exception('error:allroutesfailed', 'local_ai_bridge');
    }

    /**
     * Method normalize_messages.
     *
     * @param array $messages Parameter messages.
     * @return array Return value.
     */
    private static function normalize_messages(array $messages): array {
        $normalized = [];
        foreach ($messages as $message) {
            if (!is_array($message) || !isset($message['role'], $message['content'])) {
                throw new invalid_parameter_exception('Each AI message requires role and content.');
            }
            $role = (string)$message['role'];
            if (!in_array($role, ['system', 'user', 'assistant'], true)) {
                throw new invalid_parameter_exception('Unsupported AI message role.');
            }
            $normalized[] = ['role' => $role, 'content' => (string)$message['content']];
        }
        return $normalized;
    }
}
