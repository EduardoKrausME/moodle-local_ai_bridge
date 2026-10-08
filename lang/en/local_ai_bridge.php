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
 * local_ai_bridge.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addconnection'] = 'Add connection';
$string['addpurpose'] = 'Add purpose';
$string['addrole'] = 'Add AI role';
$string['addroute'] = 'Add route';
$string['addtenant'] = 'Add tenant';
$string['addtenantadmin'] = 'Add tenant administrator';
$string['adjustcredits'] = 'Adjust credits';
$string['adminsrestricted'] = 'Only site administrators with tenant-management capability can delegate tenant administrators.';
$string['ai_bridge:manageall'] = 'Manage all AI Bridge tenants';
$string['ai_bridge:managetenants'] = 'Create and manage AI Bridge tenants';
$string['ai_bridge:use'] = 'Use AI Bridge';
$string['ai_bridge:viewdetailedstats'] = 'View detailed AI Bridge statistics';
$string['ai_bridge:viewuserstats'] = 'View per-user AI Bridge statistics';
$string['airole'] = 'AI role';
$string['amount'] = 'Amount';
$string['anyrole'] = 'Any AI role';
$string['chooseprovider'] = 'Choose provider';
$string['connection'] = 'Connection';
$string['connectionname'] = 'Connection name';
$string['connections'] = 'Connections';
$string['creditamount'] = 'Credit amount';
$string['creditbalance'] = 'Credit balance: {$a}';
$string['creditcost'] = 'Credits per successful request';
$string['credits'] = 'Credits';
$string['creditused'] = 'Credits used';
$string['defaultrole'] = 'Default role';
$string['detailedstats'] = 'Detailed statistics';
$string['editconnection'] = 'Edit connection';
$string['editpurpose'] = 'Edit purpose';
$string['editrole'] = 'Edit AI role';
$string['editroute'] = 'Edit route';
$string['editusercontrol'] = 'Edit user AI access';
$string['enabled'] = 'Enabled';
$string['error:allroutesfailed'] = 'All configured AI routes failed.';
$string['error:bridgeunavailable'] = 'AI bridge provider “{$a}” is not installed or is unavailable.';
$string['error:configdecrypt'] = 'The AI provider configuration could not be decrypted.';
$string['error:creditlock'] = 'AI credit accounting is temporarily busy. Try the request again.';
$string['error:invalidendpoint'] = 'The provider endpoint URL is invalid.';
$string['error:noroute'] = 'No AI route is configured for this purpose and role.';
$string['error:notenant'] = 'No enabled AI tenant matches this user profile.';
$string['error:purposeunavailable'] = 'AI purpose “{$a}” is not available for this tenant.';
$string['error:tenantcredits'] = 'The tenant does not have enough AI credits.';
$string['error:usercredits'] = 'The user AI credit limit has been reached.';
$string['error:userdisabled'] = 'AI access is disabled for this user in the tenant.';
$string['error:usernotfound'] = 'No active Moodle user was found with that email address.';
$string['estimatedcost'] = 'Estimated provider cost';
$string['inputtokens'] = 'Input tokens';
$string['local/ai_bridge:manageall'] = 'Manage all AI Bridge tenants';
$string['local/ai_bridge:managetenants'] = 'Create and manage AI Bridge tenants';
$string['local/ai_bridge:use'] = 'Use AI Bridge';
$string['local/ai_bridge:viewdetailedstats'] = 'View detailed AI Bridge statistics';
$string['local/ai_bridge:viewuserstats'] = 'View per-user AI Bridge statistics';
$string['manage'] = 'Manage AI Bridge';
$string['maxoutputtokens'] = 'Maximum output tokens';
$string['model'] = 'Model';
$string['note'] = 'Note';
$string['outputtokens'] = 'Output tokens';
$string['overview'] = 'Overview';
$string['pluginname'] = 'AI Bridge';
$string['priority'] = 'Priority';
$string['privacy:metadata:admin'] = 'Delegated tenant administrator assignments.';
$string['privacy:metadata:admin:userid'] = 'The Moodle user ID assigned as tenant administrator.';
$string['privacy:metadata:credit'] = 'Credit ledger entries associated with AI use and administrative adjustments.';
$string['privacy:metadata:credit:actorid'] = 'The Moodle user who performed a credit adjustment, when applicable.';
$string['privacy:metadata:credit:amount'] = 'The number of credits added or removed.';
$string['privacy:metadata:credit:note'] = 'An optional administrative note about the credit adjustment.';
$string['privacy:metadata:credit:timecreated'] = 'When the credit ledger entry was created.';
$string['privacy:metadata:credit:type'] = 'The ledger entry type.';
$string['privacy:metadata:credit:userid'] = 'The Moodle user whose AI usage consumed credits, when applicable.';
$string['privacy:metadata:usage'] = 'AI usage metadata. Prompts and generated content are not stored.';
$string['privacy:metadata:usage:bridge'] = 'The provider subplugin used.';
$string['privacy:metadata:usage:credits'] = 'Internal tenant credits consumed.';
$string['privacy:metadata:usage:inputtokens'] = 'Input token count reported by the provider.';
$string['privacy:metadata:usage:model'] = 'The configured language model.';
$string['privacy:metadata:usage:outputtokens'] = 'Output token count reported by the provider.';
$string['privacy:metadata:usage:timecreated'] = 'When the AI request was recorded.';
$string['privacy:metadata:usage:userid'] = 'The Moodle user ID.';
$string['privacy:metadata:user'] = 'Per-tenant user controls for AI access.';
$string['privacy:metadata:user:creditlimit'] = 'The optional individual credit limit.';
$string['privacy:metadata:user:creditused'] = 'The number of tenant credits consumed by the user.';
$string['privacy:metadata:user:enabled'] = 'Whether AI access is enabled for the user.';
$string['privacy:metadata:user:roleid'] = 'The logical AI role assigned to the user.';
$string['privacy:metadata:user:userid'] = 'The Moodle user ID.';
$string['provider'] = 'Provider';
$string['purpose'] = 'Purpose';
$string['purposes'] = 'Purposes';
$string['requests'] = 'Requests';
$string['roles'] = 'AI roles';
$string['routes'] = 'Routes';
$string['setting:autocreatetenants'] = 'Automatically create tenants';
$string['setting:autocreatetenants_desc'] = 'Create tenant records from user profile fields when a matching tenant does not exist.';
$string['setting:logfailures'] = 'Log failed requests';
$string['setting:logfailures_desc'] = 'Store provider/model/error-code metadata for failed routes. Prompts and responses are never stored.';
$string['setting:tenantkey'] = 'Tenant profile key';
$string['setting:tenantkey_desc'] = 'Select which standard Moodle user fields identify a tenant.';
$string['subplugintype_aibridge'] = 'AI bridge provider';
$string['subplugintype_aibridge_plural'] = 'AI bridge providers';
$string['systeminstruction'] = 'System instruction';
$string['task:synctenants'] = 'Synchronize AI Bridge tenants from user profiles';
$string['temperature'] = 'Temperature';
$string['tenantadmins'] = 'Tenant administrators';
$string['tenantkey:department'] = 'Department';
$string['tenantkey:institution'] = 'Institution';
$string['tenantkey:institution_department'] = 'Institution + department';
$string['tenantprofile'] = 'Profile mapping: institution “{$a->institution}”, department “{$a->department}”.';
$string['totaltokens'] = 'Total tokens';
$string['type'] = 'Type';
$string['usercreditlimit'] = 'Individual credit limit';
$string['usercreditlimit_help'] = 'Leave empty to use only the shared tenant balance. The counter is cumulative for this tenant user record.';
$string['usershint'] = 'User activation, AI role and individual credit limit are stored per tenant. Users without an explicit record inherit the tenant default role and start enabled.';
$string['userstats'] = 'Per-user statistics';
