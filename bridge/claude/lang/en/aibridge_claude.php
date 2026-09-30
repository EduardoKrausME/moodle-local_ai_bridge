<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Claude bridge';
$string['description'] = 'Connects Moodle AI Bridge to the Anthropic Claude Messages API.';
$string['apikey'] = 'API key';
$string['baseurl'] = 'Base URL';
$string['apiversion'] = 'Anthropic API version';
$string['betas'] = 'Anthropic beta features';
$string['betas_help'] = 'Optional comma-separated beta identifiers sent in the anthropic-beta header.';
$string['timeout'] = 'Timeout (seconds)';
$string['defaultmaxoutputtokens'] = 'Default maximum output tokens';
$string['inputcost'] = 'Regular input cost per 1M tokens';
$string['outputcost'] = 'Output cost per 1M tokens';
$string['cachewritecost'] = 'Cache write cost per 1M tokens';
$string['cachereadcost'] = 'Cache read cost per 1M tokens';
$string['websearchcost'] = 'Web search cost per 1,000 searches';
$string['error:request'] = 'Claude request failed: {$a}';
$string['error:response'] = 'Claude returned an invalid response.';
$string['error:invalidversion'] = 'The Anthropic API version must use YYYY-MM-DD format.';
$string['error:invalidbeta'] = 'The Anthropic beta list contains invalid characters.';
$string['error:invalidheader'] = 'A Claude HTTP header value contains invalid line-break characters.';
$string['error:invalidrole'] = 'Claude Messages API accepts only user and assistant conversation roles; system messages are sent separately.';
$string['privacy:metadata'] = 'The Claude bridge stores no personal data of its own. Configuration and usage metadata are stored by local_ai_bridge.';
