<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

use local_ai_bridge\local\bridge\response;

final class usage_logger {
    public static function success(\stdClass $tenant, int $userid, \stdClass $purpose, \stdClass $usercontrol,
            \stdClass $route, response $response, int $latencyms): int {
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

    public static function failure(\stdClass $tenant, int $userid, \stdClass $purpose, \stdClass $usercontrol,
            \stdClass $route, \Throwable $exception, int $latencyms): void {
        global $DB;
        if (!get_config('local_ai_bridge', 'logfailures')) {
            return;
        }
        $code = $exception instanceof \moodle_exception ? $exception->errorcode : get_class($exception);
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
