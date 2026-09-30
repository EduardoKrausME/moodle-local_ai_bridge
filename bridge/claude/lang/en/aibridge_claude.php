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
 * aibridge_claude.php
 *
 * @package   aibridge_claude
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apikey'] = 'API key';
$string['apiversion'] = 'Anthropic API version';
$string['baseurl'] = 'Base URL';
$string['betas'] = 'Anthropic beta features';
$string['betas_help'] = 'Optional comma-separated beta identifiers sent in the anthropic-beta header.';
$string['cachereadcost'] = 'Cache read cost per 1M tokens';
$string['cachewritecost'] = 'Cache write cost per 1M tokens';
$string['defaultmaxoutputtokens'] = 'Default maximum output tokens';
$string['description'] = 'Connects Moodle AI Bridge to the Anthropic Claude Messages API.';
$string['error:invalidbeta'] = 'The Anthropic beta list contains invalid characters.';
$string['error:invalidheader'] = 'A Claude HTTP header value contains invalid line-break characters.';
$string['error:invalidrole'] = 'Claude Messages API accepts only user and assistant conversation roles; system messages are sent separately.';
$string['error:invalidversion'] = 'The Anthropic API version must use YYYY-MM-DD format.';
$string['error:request'] = 'Claude request failed: {$a}';
$string['error:response'] = 'Claude returned an invalid response.';
$string['inputcost'] = 'Regular input cost per 1M tokens';
$string['outputcost'] = 'Output cost per 1M tokens';
$string['pluginname'] = 'Claude bridge';
$string['privacy:metadata'] = 'The Claude bridge stores no personal data of its own. Configuration and usage metadata are stored by local_ai_bridge.';
$string['timeout'] = 'Timeout (seconds)';
$string['websearchcost'] = 'Web search cost per 1,000 searches';
