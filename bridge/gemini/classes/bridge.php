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
 * bridge.php
 *
 * @package   aibridge_gemini
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aibridge_gemini;

use curl;
use local_ai_bridge\bridge\provider_interface;
use local_ai_bridge\bridge\request;
use local_ai_bridge\bridge\response;
use local_ai_bridge\security\url_guard;
use moodle_exception;
use MoodleQuickForm;

/**
 * Class bridge.
 */
class bridge implements provider_interface {
    /**
     * Method get_component.
     *
     * @return string Return value.
     */
    public function get_component(): string {
        return 'aibridge_gemini';
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('pluginname', 'aibridge_gemini');
    }

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string {
        return get_string('description', 'aibridge_gemini');
    }

    /**
     * Method get_default_config.
     *
     * @return array Return value.
     */
    public function get_default_config(): array {
        return [
            'baseurl' => 'https://generativelanguage.googleapis.com/v1beta',
            'timeout' => 60,
            'inputcost' => 0,
            'outputcost' => 0,
        ];
    }

    /**
     * Method get_secret_fields.
     *
     * @return array Return value.
     */
    public function get_secret_fields(): array {
        return ['apikey'];
    }

    /**
     * Method add_config_form_elements.
     *
     * @param MoodleQuickForm $mform Parameter mform.
     * @param string $prefix Parameter prefix.
     * @return void Return value.
     */
    public function add_config_form_elements(MoodleQuickForm $mform, string $prefix): void {
        $mform->addElement('passwordunmask', $prefix . 'apikey', get_string('apikey', 'aibridge_gemini'));
        $mform->setType($prefix . 'apikey', PARAM_RAW_TRIMMED);
        $mform->addRule($prefix . 'apikey', null, 'required', null, 'client');
        $mform->addElement('text', $prefix . 'baseurl', get_string('baseurl', 'aibridge_gemini'));
        $mform->setType($prefix . 'baseurl', PARAM_URL);
        $mform->addElement('text', $prefix . 'timeout', get_string('timeout', 'aibridge_gemini'));
        $mform->setType($prefix . 'timeout', PARAM_INT);
        $mform->addElement('text', $prefix . 'inputcost', get_string('inputcost', 'aibridge_gemini'));
        $mform->setType($prefix . 'inputcost', PARAM_FLOAT);
        $mform->addElement('text', $prefix . 'outputcost', get_string('outputcost', 'aibridge_gemini'));
        $mform->setType($prefix . 'outputcost', PARAM_FLOAT);
    }

    /**
     * Method validate_config.
     *
     * @param array $config Parameter config.
     * @return array Return value.
     */
    public function validate_config(array $config): array {
        $errors = [];
        if (empty($config['apikey'])) {
            $errors['apikey'] = get_string('required');
        }
        if ($error = url_guard::validate((string)($config['baseurl'] ?? ''))) {
            $errors['baseurl'] = $error;
        }
        return $errors;
    }

    /**
     * Method generate.
     *
     * @param request $request Parameter request.
     * @param array $config Parameter config.
     * @param string $model Parameter model.
     * @return response Return value.
     */
    public function generate(request $request, array $config, string $model): response {
        $baseurl = rtrim((string)$config['baseurl'], '/');
        if ($error = url_guard::validate($baseurl)) {
            throw new moodle_exception('error:invalidendpoint', 'local_ai_bridge');
        }
        $url = $baseurl . '/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode((string)$config['apikey']);
        $contents = [];
        $system = [];
        foreach ($request->messages as $message) {
            if ($message['role'] === 'system') {
                $system[] = $message['content'];
                continue;
            }
            $contents[] = [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [
                    [
                        'text' => $message['content'],
                    ],
                ],
            ];
        }
        $payload = ['contents' => $contents];
        if ($system) {
            $payload['systemInstruction'] = ['parts' => [['text' => implode("\n", $system)]]];
        }
        $generation = [];
        if ($request->temperature !== null) {
            $generation['temperature'] = $request->temperature;
        }
        if ($request->maxoutputtokens !== null) {
            $generation['maxOutputTokens'] = $request->maxoutputtokens;
        }
        if ($generation) {
            $payload['generationConfig'] = $generation;
        }
        $payload = array_replace_recursive($payload, $request->options);
        $curl = new curl();
        $curl->setHeader(['Content-Type: application/json']);
        $raw = $curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR),
            ['CURLOPT_TIMEOUT' => max(1, (int)($config['timeout'] ?? 60))]);
        $info = $curl->get_info();
        $status = (int)($info['http_code'] ?? 0);
        $data = json_decode((string)$raw, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($data) ? (string)($data['error']['message'] ?? 'HTTP ' . $status) : 'HTTP ' . $status;
            throw new moodle_exception('error:request', 'aibridge_gemini', '', $message);
        }
        if (!is_array($data)) {
            throw new moodle_exception('error:response', 'aibridge_gemini');
        }
        $parts = [];
        foreach (($data['candidates'][0]['content']['parts'] ?? []) as $part) {
            if (isset($part['text'])) {
                $parts[] = (string)$part['text'];
            }
        }
        $usage = $data['usageMetadata'] ?? [];
        $input = (int)($usage['promptTokenCount'] ?? 0);
        $output = (int)($usage['candidatesTokenCount'] ?? 0);
        $total = (int)($usage['totalTokenCount'] ?? ($input + $output));
        $cost = ($input / 1_000_000) *
            (float)($config['inputcost'] ?? 0) + ($output / 1_000_000) *
            (float)($config['outputcost'] ?? 0);
        return new response(implode("\n", $parts), $model, $input, $output, $total, $cost, []);
    }
}
