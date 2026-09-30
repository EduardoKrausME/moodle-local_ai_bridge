<?php
namespace aibridge_claude;

defined('MOODLE_INTERNAL') || die();

use local_ai_bridge\local\bridge\provider_interface;
use local_ai_bridge\local\bridge\request;
use local_ai_bridge\local\bridge\response;
use local_ai_bridge\local\security\url_guard;

/**
 * Anthropic Claude Messages API bridge.
 *
 * Everything specific to Anthropic lives in this subplugin: authentication, API
 * versioning, payload translation, response parsing, usage accounting and cost estimation.
 */
final class bridge implements provider_interface {
    public function get_component(): string {
        return 'aibridge_claude';
    }

    public function get_name(): string {
        return get_string('pluginname', 'aibridge_claude');
    }

    public function get_description(): string {
        return get_string('description', 'aibridge_claude');
    }

    public function get_default_config(): array {
        return [
            'baseurl' => 'https://api.anthropic.com/v1',
            'apiversion' => '2023-06-01',
            'betas' => '',
            'timeout' => 60,
            'defaultmaxoutputtokens' => 4096,
            'inputcost' => 0,
            'outputcost' => 0,
            'cachewritecost' => 0,
            'cachereadcost' => 0,
            'websearchcost' => 0,
        ];
    }

    public function get_secret_fields(): array {
        return ['apikey'];
    }

    public function add_config_form_elements(\MoodleQuickForm $mform, string $prefix): void {
        $mform->addElement('passwordunmask', $prefix . 'apikey', get_string('apikey', 'aibridge_claude'));
        $mform->setType($prefix . 'apikey', PARAM_RAW_TRIMMED);
        $mform->addRule($prefix . 'apikey', null, 'required', null, 'client');

        $mform->addElement('text', $prefix . 'baseurl', get_string('baseurl', 'aibridge_claude'));
        $mform->setType($prefix . 'baseurl', PARAM_URL);

        $mform->addElement('text', $prefix . 'apiversion', get_string('apiversion', 'aibridge_claude'));
        $mform->setType($prefix . 'apiversion', PARAM_TEXT);

        $mform->addElement('text', $prefix . 'betas', get_string('betas', 'aibridge_claude'));
        $mform->setType($prefix . 'betas', PARAM_TEXT);
        $mform->addHelpButton($prefix . 'betas', 'betas', 'aibridge_claude');

        $mform->addElement('text', $prefix . 'timeout', get_string('timeout', 'aibridge_claude'));
        $mform->setType($prefix . 'timeout', PARAM_INT);

        $mform->addElement(
            'text',
            $prefix . 'defaultmaxoutputtokens',
            get_string('defaultmaxoutputtokens', 'aibridge_claude')
        );
        $mform->setType($prefix . 'defaultmaxoutputtokens', PARAM_INT);

        $mform->addElement('text', $prefix . 'inputcost', get_string('inputcost', 'aibridge_claude'));
        $mform->setType($prefix . 'inputcost', PARAM_FLOAT);

        $mform->addElement('text', $prefix . 'outputcost', get_string('outputcost', 'aibridge_claude'));
        $mform->setType($prefix . 'outputcost', PARAM_FLOAT);

        $mform->addElement('text', $prefix . 'cachewritecost', get_string('cachewritecost', 'aibridge_claude'));
        $mform->setType($prefix . 'cachewritecost', PARAM_FLOAT);

        $mform->addElement('text', $prefix . 'cachereadcost', get_string('cachereadcost', 'aibridge_claude'));
        $mform->setType($prefix . 'cachereadcost', PARAM_FLOAT);

        $mform->addElement('text', $prefix . 'websearchcost', get_string('websearchcost', 'aibridge_claude'));
        $mform->setType($prefix . 'websearchcost', PARAM_FLOAT);
    }

    public function validate_config(array $config): array {
        $errors = [];

        if (empty($config['apikey'])) {
            $errors['apikey'] = get_string('required');
        } elseif ($this->contains_line_break((string)$config['apikey'])) {
            $errors['apikey'] = get_string('error:invalidheader', 'aibridge_claude');
        }

        if ($error = url_guard::validate((string)($config['baseurl'] ?? ''))) {
            $errors['baseurl'] = $error;
        }

        $apiversion = trim((string)($config['apiversion'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $apiversion)) {
            $errors['apiversion'] = get_string('error:invalidversion', 'aibridge_claude');
        }

        $betas = trim((string)($config['betas'] ?? ''));
        if ($this->contains_line_break($betas) || ($betas !== '' && !preg_match('/^[A-Za-z0-9._, -]+$/', $betas))) {
            $errors['betas'] = get_string('error:invalidbeta', 'aibridge_claude');
        }

        if ((int)($config['timeout'] ?? 0) < 1) {
            $errors['timeout'] = get_string('invaliddata');
        }
        if ((int)($config['defaultmaxoutputtokens'] ?? 0) < 1) {
            $errors['defaultmaxoutputtokens'] = get_string('invaliddata');
        }

        foreach (['inputcost', 'outputcost', 'cachewritecost', 'cachereadcost', 'websearchcost'] as $field) {
            if ((float)($config[$field] ?? 0) < 0) {
                $errors[$field] = get_string('invaliddata');
            }
        }

        return $errors;
    }

    public function generate(request $request, array $config, string $model): response {
        $baseurl = rtrim((string)($config['baseurl'] ?? ''), '/');
        if ($error = url_guard::validate($baseurl)) {
            throw new \moodle_exception('error:invalidendpoint', 'local_ai_bridge');
        }

        $headers = $this->build_headers($config);
        $payload = $this->build_payload($request, $config, $model);

        $curl = new \curl();
        $curl->setHeader($headers);
        $raw = $curl->post(
            $baseurl . '/messages',
            json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            ['CURLOPT_TIMEOUT' => max(1, (int)($config['timeout'] ?? 60))]
        );

        $info = $curl->get_info();
        $status = (int)($info['http_code'] ?? 0);
        $data = json_decode((string)$raw, true);

        if ($status < 200 || $status >= 300) {
            $message = 'HTTP ' . $status;
            if (is_array($data)) {
                $message = (string)($data['error']['message'] ?? $data['message'] ?? $message);
            }
            throw new \moodle_exception('error:request', 'aibridge_claude', '', $message);
        }

        if (!is_array($data) || !isset($data['content']) || !is_array($data['content'])) {
            throw new \moodle_exception('error:response', 'aibridge_claude');
        }

        $text = $this->extract_text($data['content']);
        $usage = is_array($data['usage'] ?? null) ? $data['usage'] : [];

        $regularinput = (int)($usage['input_tokens'] ?? 0);
        $cachewrite = (int)($usage['cache_creation_input_tokens'] ?? 0);
        $cacheread = (int)($usage['cache_read_input_tokens'] ?? 0);
        $output = (int)($usage['output_tokens'] ?? 0);
        $input = $regularinput + $cachewrite + $cacheread;
        $total = $input + $output;
        $websearches = (int)($usage['server_tool_use']['web_search_requests'] ?? 0);

        $cost = ($regularinput / 1_000_000) * (float)($config['inputcost'] ?? 0)
            + ($cachewrite / 1_000_000) * (float)($config['cachewritecost'] ?? 0)
            + ($cacheread / 1_000_000) * (float)($config['cachereadcost'] ?? 0)
            + ($output / 1_000_000) * (float)($config['outputcost'] ?? 0)
            + ($websearches / 1000) * (float)($config['websearchcost'] ?? 0);

        return new response(
            $text,
            (string)($data['model'] ?? $model),
            $input,
            $output,
            $total,
            $cost,
            [
                'id' => $data['id'] ?? null,
                'stop_reason' => $data['stop_reason'] ?? null,
                'stop_sequence' => $data['stop_sequence'] ?? null,
                'regular_input_tokens' => $regularinput,
                'cache_creation_input_tokens' => $cachewrite,
                'cache_read_input_tokens' => $cacheread,
                'web_search_requests' => $websearches,
            ]
        );
    }

    private function build_headers(array $config): array {
        $apikey = trim((string)($config['apikey'] ?? ''));
        $apiversion = trim((string)($config['apiversion'] ?? '2023-06-01'));
        $betas = trim((string)($config['betas'] ?? ''));

        foreach ([$apikey, $apiversion, $betas] as $value) {
            if ($this->contains_line_break($value)) {
                throw new \moodle_exception('error:invalidheader', 'aibridge_claude');
            }
        }

        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $apikey,
            'anthropic-version: ' . $apiversion,
        ];
        if ($betas !== '') {
            $headers[] = 'anthropic-beta: ' . $betas;
        }
        return $headers;
    }

    private function build_payload(request $request, array $config, string $model): array {
        [$system, $messages] = $this->normalise_messages($request->messages);

        $payload = [
            'model' => $model,
            'max_tokens' => $request->maxoutputtokens ?? max(1, (int)($config['defaultmaxoutputtokens'] ?? 4096)),
            'messages' => $messages,
        ];
        if ($system !== null) {
            $payload['system'] = $system;
        }

        // Temperature is intentionally not copied automatically. Anthropic has deprecated
        // it for newer Claude models. Advanced callers can still add it explicitly through
        // request options when targeting a model that supports it.
        return $payload + $request->options;
    }

    private function normalise_messages(array $messages): array {
        $systemblocks = [];
        $conversation = [];

        foreach ($messages as $message) {
            if (!is_array($message)) {
                continue;
            }
            $role = (string)($message['role'] ?? '');
            $content = $message['content'] ?? '';

            if ($role === 'system') {
                $this->append_system_content($systemblocks, $content);
                continue;
            }

            if (!in_array($role, ['user', 'assistant'], true)) {
                throw new \moodle_exception('error:invalidrole', 'aibridge_claude');
            }

            $conversation[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        $system = null;
        if ($systemblocks !== []) {
            if (count($systemblocks) === 1 && isset($systemblocks[0]['text'])) {
                $system = (string)$systemblocks[0]['text'];
            } else {
                $system = $systemblocks;
            }
        }

        return [$system, $conversation];
    }

    private function append_system_content(array &$blocks, mixed $content): void {
        if (is_string($content)) {
            if ($content !== '') {
                $blocks[] = ['type' => 'text', 'text' => $content];
            }
            return;
        }

        if (!is_array($content)) {
            return;
        }

        foreach ($content as $block) {
            if (is_string($block)) {
                $blocks[] = ['type' => 'text', 'text' => $block];
            } elseif (is_array($block)) {
                $blocks[] = $block;
            }
        }
    }

    private function extract_text(array $content): string {
        $parts = [];
        foreach ($content as $block) {
            if (!is_array($block)) {
                continue;
            }
            if (($block['type'] ?? '') === 'text' && isset($block['text'])) {
                $parts[] = (string)$block['text'];
            }
        }
        return implode("\n", $parts);
    }

    private function contains_line_break(string $value): bool {
        return str_contains($value, "\r") || str_contains($value, "\n");
    }
}
