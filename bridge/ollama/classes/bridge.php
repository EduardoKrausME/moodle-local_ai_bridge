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
 * @package   aibridge_ollama
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aibridge_ollama;

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
final class bridge implements provider_interface {
    /**
     * Method get_component.
     *
     * @return string Return value.
     */
    public function get_component(): string {
        return 'aibridge_ollama';
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string {
        return get_string('pluginname', 'aibridge_ollama');
    }

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string {
        return get_string('description', 'aibridge_ollama');
    }

    /**
     * Method get_default_config.
     *
     * @return array Return value.
     */
    public function get_default_config(): array {
        return ['baseurl' => 'http://localhost:11434', 'timeout' => 120, 'apikey' => ''];
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
        $mform->addElement('text', $prefix . 'baseurl', get_string('baseurl', 'aibridge_ollama'));
        $mform->setType($prefix . 'baseurl', PARAM_URL);
        $mform->addElement('passwordunmask', $prefix . 'apikey', get_string('apikey', 'aibridge_ollama'));
        $mform->setType($prefix . 'apikey', PARAM_RAW_TRIMMED);
        $mform->addElement('text', $prefix . 'timeout', get_string('timeout', 'aibridge_ollama'));
        $mform->setType($prefix . 'timeout', PARAM_INT);
    }

    /**
     * Method validate_config.
     *
     * @param array $config Parameter config.
     * @return array Return value.
     */
    public function validate_config(array $config): array {
        $errors = [];
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
        $payload = ['model' => $model, 'messages' => $request->messages, 'stream' => false];
        $options = [];
        if ($request->temperature !== null) {
            $options['temperature'] = $request->temperature;
        }
        if ($request->maxoutputtokens !== null) {
            $options['num_predict'] = $request->maxoutputtokens;
        }
        if ($options) {
            $payload['options'] = $options;
        }
        $payload = array_replace_recursive($payload, $request->options);
        $headers = ['Content-Type: application/json'];
        if (!empty($config['apikey'])) {
            $headers[] = 'Authorization: Bearer ' . $config['apikey'];
        }
        $curl = new curl();
        $curl->setHeader($headers);
        $raw = $curl->post($baseurl . '/api/chat', json_encode($payload, JSON_THROW_ON_ERROR), ['CURLOPT_TIMEOUT' => max(1, (int)($config['timeout'] ?? 120))]);
        $info = $curl->get_info();
        $status = (int)($info['http_code'] ?? 0);
        $data = json_decode((string)$raw, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($data) ? (string)($data['error'] ?? 'HTTP ' . $status) : 'HTTP ' . $status;
            throw new moodle_exception('error:request', 'aibridge_ollama', '', $message);
        }
        if (!is_array($data) || !isset($data['message']['content'])) {
            throw new moodle_exception('error:response', 'aibridge_ollama');
        }
        $input = (int)($data['prompt_eval_count'] ?? 0);
        $output = (int)($data['eval_count'] ?? 0);
        return new response((string)$data['message']['content'], (string)($data['model'] ?? $model), $input, $output, $input + $output, 0.0, []);
    }
}
