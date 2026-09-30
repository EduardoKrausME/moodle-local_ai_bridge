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
 * aibridge_ollama.php
 *
 * @package   aibridge_ollama
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apikey'] = 'Optional bearer token';
$string['baseurl'] = 'Ollama Base URL';
$string['description'] = 'Connects Moodle AI Bridge to a self-hosted Ollama chat endpoint.';
$string['error:request'] = 'Ollama request failed: {$a}';
$string['error:response'] = 'Ollama returned an invalid response.';
$string['pluginname'] = 'Ollama bridge';
$string['privacy:metadata'] = 'The Ollama bridge stores no personal data of its own. Configuration and usage metadata are stored by local_ai_bridge.';
$string['timeout'] = 'Timeout (seconds)';
