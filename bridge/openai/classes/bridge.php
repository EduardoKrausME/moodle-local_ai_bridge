<?php
namespace aibridge_openai;

defined('MOODLE_INTERNAL') || die();

use local_ai_bridge\local\bridge\provider_interface;
use local_ai_bridge\local\bridge\request;
use local_ai_bridge\local\bridge\response;
use local_ai_bridge\local\security\url_guard;

final class bridge implements provider_interface {
    public function get_component(): string { return 'aibridge_openai'; }
    public function get_name(): string { return get_string('pluginname', 'aibridge_openai'); }
    public function get_description(): string { return get_string('description', 'aibridge_openai'); }
    public function get_default_config(): array {
        return ['baseurl' => 'https://api.openai.com/v1', 'apimode' => 'responses', 'timeout' => 60,
            'inputcost' => 0, 'outputcost' => 0, 'project' => '', 'organization' => ''];
    }
    public function get_secret_fields(): array { return ['apikey']; }
    public function add_config_form_elements(\MoodleQuickForm $mform, string $prefix): void {
        $mform->addElement('passwordunmask', $prefix . 'apikey', get_string('apikey', 'aibridge_openai'));
        $mform->setType($prefix . 'apikey', PARAM_RAW_TRIMMED);
        $mform->addRule($prefix . 'apikey', null, 'required', null, 'client');
        $mform->addElement('text', $prefix . 'baseurl', get_string('baseurl', 'aibridge_openai'));
        $mform->setType($prefix . 'baseurl', PARAM_URL);
        $mform->addElement('select', $prefix . 'apimode', get_string('apimode', 'aibridge_openai'), [
            'responses' => get_string('responses', 'aibridge_openai'),
            'chatcompletions' => get_string('chatcompletions', 'aibridge_openai'),
        ]);
        $mform->addElement('text', $prefix . 'project', get_string('project', 'aibridge_openai'));
        $mform->setType($prefix . 'project', PARAM_TEXT);
        $mform->addElement('text', $prefix . 'organization', get_string('organization', 'aibridge_openai'));
        $mform->setType($prefix . 'organization', PARAM_TEXT);
        $mform->addElement('text', $prefix . 'timeout', get_string('timeout', 'aibridge_openai'));
        $mform->setType($prefix . 'timeout', PARAM_INT);
        $mform->addElement('text', $prefix . 'inputcost', get_string('inputcost', 'aibridge_openai'));
        $mform->setType($prefix . 'inputcost', PARAM_FLOAT);
        $mform->addElement('text', $prefix . 'outputcost', get_string('outputcost', 'aibridge_openai'));
        $mform->setType($prefix . 'outputcost', PARAM_FLOAT);
    }
    public function validate_config(array $config): array {
        $errors = [];
        if (empty($config['apikey'])) { $errors['apikey'] = get_string('required'); }
        if ($error = url_guard::validate((string)($config['baseurl'] ?? ''))) { $errors['baseurl'] = $error; }
        if (!in_array($config['apimode'] ?? '', ['responses', 'chatcompletions'], true)) { $errors['apimode'] = get_string('invaliddata'); }
        return $errors;
    }
    public function generate(request $request, array $config, string $model): response {
        $baseurl = rtrim((string)$config['baseurl'], '/');
        if ($error = url_guard::validate($baseurl)) { throw new \moodle_exception('error:invalidendpoint', 'local_ai_bridge'); }
        $mode = $config['apimode'] ?? 'responses';
        $url = $baseurl . ($mode === 'chatcompletions' ? '/chat/completions' : '/responses');
        $headers = ['Authorization: Bearer ' . $config['apikey'], 'Content-Type: application/json'];
        if (!empty($config['project'])) { $headers[] = 'OpenAI-Project: ' . $config['project']; }
        if (!empty($config['organization'])) { $headers[] = 'OpenAI-Organization: ' . $config['organization']; }
        $payload = $mode === 'chatcompletions' ? $this->chat_payload($request, $model) : $this->responses_payload($request, $model);
        $curl = new \curl();
        $curl->setHeader($headers);
        $options = ['CURLOPT_TIMEOUT' => max(1, (int)($config['timeout'] ?? 60))];
        $raw = $curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR), $options);
        $info = $curl->get_info();
        $status = (int)($info['http_code'] ?? 0);
        $data = json_decode((string)$raw, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($data) ? (string)($data['error']['message'] ?? 'HTTP ' . $status) : 'HTTP ' . $status;
            throw new \moodle_exception('error:request', 'aibridge_openai', '', $message);
        }
        if (!is_array($data)) { throw new \moodle_exception('error:response', 'aibridge_openai'); }
        if ($mode === 'chatcompletions') {
            $text = (string)($data['choices'][0]['message']['content'] ?? '');
            $input = (int)($data['usage']['prompt_tokens'] ?? 0);
            $output = (int)($data['usage']['completion_tokens'] ?? 0);
            $total = (int)($data['usage']['total_tokens'] ?? ($input + $output));
        } else {
            $text = $this->extract_responses_text($data);
            $input = (int)($data['usage']['input_tokens'] ?? 0);
            $output = (int)($data['usage']['output_tokens'] ?? 0);
            $total = (int)($data['usage']['total_tokens'] ?? ($input + $output));
        }
        $cost = ($input / 1_000_000) * (float)($config['inputcost'] ?? 0) +
            ($output / 1_000_000) * (float)($config['outputcost'] ?? 0);
        return new response($text, (string)($data['model'] ?? $model), $input, $output, $total, $cost, ['id' => $data['id'] ?? null]);
    }
    private function chat_payload(request $request, string $model): array {
        $payload = ['model' => $model, 'messages' => $request->messages];
        if ($request->temperature !== null) { $payload['temperature'] = $request->temperature; }
        if ($request->maxoutputtokens !== null) { $payload['max_completion_tokens'] = $request->maxoutputtokens; }
        return $payload + $request->options;
    }
    private function responses_payload(request $request, string $model): array {
        $payload = ['model' => $model, 'input' => $request->messages];
        if ($request->temperature !== null) { $payload['temperature'] = $request->temperature; }
        if ($request->maxoutputtokens !== null) { $payload['max_output_tokens'] = $request->maxoutputtokens; }
        return $payload + $request->options;
    }
    private function extract_responses_text(array $data): string {
        if (isset($data['output_text']) && is_string($data['output_text'])) { return $data['output_text']; }
        $parts = [];
        foreach (($data['output'] ?? []) as $item) {
            foreach (($item['content'] ?? []) as $content) {
                if (($content['type'] ?? '') === 'output_text' && isset($content['text'])) { $parts[] = (string)$content['text']; }
            }
        }
        return implode("\n", $parts);
    }
}
