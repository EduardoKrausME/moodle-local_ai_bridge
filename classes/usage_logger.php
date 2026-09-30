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
 * usage_logger.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use local_ai_bridge\bridge\response;
use moodle_exception;
use stdClass;
use Throwable;

/**
 * Class usage_logger.
 */
final class usage_logger {
    /**
     * Method success.
     *
     * @param stdClass $tenant Parameter tenant.
     * @param int $userid Parameter userid.
     * @param stdClass $purpose Parameter purpose.
     * @param stdClass $usercontrol Parameter usercontrol.
     * @param stdClass $route Parameter route.
     * @param response $response Parameter response.
     * @param int $latencyms Parameter latencyms.
     * @return int Return value.
     */
    public static function success(stdClass $tenant, int $userid, stdClass $purpose, stdClass $usercontrol,
                                   stdClass $route, response $response, int $latencyms): int {
        global $DB;
        return $DB->insert_record('local_ai_bridge_usage', (object)[
            'tenantid' => $tenant->id,
            'userid' => $userid,
            'purposeid' => $purpose->id,
            'roleid' => $usercontrol->roleid ?: null,
            'connectionid' => $route->connectionid,
            'bridge' => $route->bridge,
            'model' => $response->model ?: $route->model,
            'inputtokens' => $response->inputtokens,
            'outputtokens' => $response->outputtokens,
            'totaltokens' => $response->totaltokens,
            'credits' => (float)$purpose->creditcost,
            'estimatedcost' => $response->estimatedcost,
            'latencyms' => $latencyms,
            'status' => 'success',
            'errorcode' => null,
            'timecreated' => time(),
        ]);
    }

    /**
     * Method failure.
     *
     * @param stdClass $tenant Parameter tenant.
     * @param int $userid Parameter userid.
     * @param stdClass $purpose Parameter purpose.
     * @param stdClass $usercontrol Parameter usercontrol.
     * @param stdClass $route Parameter route.
     * @param Throwable $exception Parameter exception.
     * @param int $latencyms Parameter latencyms.
     * @return void Return value.
     */
    public static function failure(stdClass $tenant, int $userid, stdClass $purpose, stdClass $usercontrol,
                                   stdClass $route, Throwable $exception, int $latencyms): void {
        global $DB;
        if (!get_config('local_ai_bridge', 'logfailures')) {
            return;
        }
        $code = $exception instanceof moodle_exception ? $exception->errorcode : get_class($exception);
        $DB->insert_record('local_ai_bridge_usage', (object)[
            'tenantid' => $tenant->id,
            'userid' => $userid,
            'purposeid' => $purpose->id,
            'roleid' => $usercontrol->roleid ?: null,
            'connectionid' => $route->connectionid,
            'bridge' => $route->bridge,
            'model' => $route->model,
            'inputtokens' => 0,
            'outputtokens' => 0,
            'totaltokens' => 0,
            'credits' => 0,
            'estimatedcost' => 0,
            'latencyms' => $latencyms,
            'status' => 'error',
            'errorcode' => substr((string)$code, 0, 100),
            'timecreated' => time(),
        ]);
    }
}
