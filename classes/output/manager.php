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
 * manager.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\output;

/**
 * Class manager.
 */
final class manager {
    /**
     * Method overview.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function overview(\stdClass $tenant): string {
        global $DB;
        $counts = [
            get_string('purposes', 'local_ai_bridge') => $DB->count_records('local_ai_bridge_purpose', ['tenantid' => $tenant->id, 'enabled' => 1]),
            get_string('connections', 'local_ai_bridge') => $DB->count_records('local_ai_bridge_connection', ['tenantid' => $tenant->id, 'enabled' => 1]),
            get_string('routes', 'local_ai_bridge') => $DB->count_records('local_ai_bridge_route', ['tenantid' => $tenant->id, 'enabled' => 1]),
        ];
        $html = \html_writer::tag('p', get_string('tenantprofile', 'local_ai_bridge', (object)[
            'institution' => s($tenant->institution), 'department' => s($tenant->department)]));
        $html .= \html_writer::tag('p', get_string('creditbalance', 'local_ai_bridge', format_float((float)$tenant->creditbalance, 4)));
        $items = [];
        foreach ($counts as $label => $value) {
            $items[] = \html_writer::tag('strong', (string)$value) . ' ' . s($label);
        }
        return $html . \html_writer::alist($items);
    }

    /**
     * Method purposes.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function purposes(\stdClass $tenant): string {
        global $DB;
        $records = $DB->get_records('local_ai_bridge_purpose', ['tenantid' => $tenant->id], 'name ASC');
        $url = new \moodle_url('/local/ai_bridge/purpose.php', ['tenantid' => $tenant->id]);
        $html = \html_writer::link($url, get_string('addpurpose', 'local_ai_bridge'), ['class' => 'btn btn-primary mb-3']);
        $table = new \html_table();
        $table->head = [get_string('name'), get_string('idnumber'), get_string('creditcost', 'local_ai_bridge'), get_string('enabled', 'local_ai_bridge'), get_string('actions')];
        foreach ($records as $record) {
            $edit = new \moodle_url('/local/ai_bridge/purpose.php', ['tenantid' => $tenant->id, 'id' => $record->id]);
            $table->data[] = [format_string($record->name), s($record->idnumber), format_float((float)$record->creditcost, 4), $record->enabled ? get_string('yes') : get_string('no'), \html_writer::link($edit, get_string('edit'))];
        }
        return $html . \html_writer::table($table);
    }

    /**
     * Method connections.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function connections(\stdClass $tenant): string {
        global $DB;
        $records = $DB->get_records('local_ai_bridge_connection', ['tenantid' => $tenant->id], 'name ASC');
        $url = new \moodle_url('/local/ai_bridge/connection.php', ['tenantid' => $tenant->id]);
        $html = \html_writer::link($url, get_string('addconnection', 'local_ai_bridge'), ['class' => 'btn btn-primary mb-3']);
        $table = new \html_table();
        $table->head = [get_string('name'), get_string('provider', 'local_ai_bridge'), get_string('enabled', 'local_ai_bridge'), get_string('actions')];
        foreach ($records as $record) {
            $edit = new \moodle_url('/local/ai_bridge/connection.php', ['tenantid' => $tenant->id, 'id' => $record->id]);
            $table->data[] = [format_string($record->name), s($record->bridge), $record->enabled ? get_string('yes') : get_string('no'), \html_writer::link($edit, get_string('edit'))];
        }
        return $html . \html_writer::table($table);
    }

    /**
     * Method routes.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function routes(\stdClass $tenant): string {
        global $DB;
        $sql = "SELECT r.*, p.name AS purposename, c.name AS connectionname, ar.name AS rolename
                  FROM {local_ai_bridge_route} r
                  JOIN {local_ai_bridge_purpose} p ON p.id = r.purposeid
                  JOIN {local_ai_bridge_connection} c ON c.id = r.connectionid
             LEFT JOIN {local_ai_bridge_role} ar ON ar.id = r.roleid
                 WHERE r.tenantid = :tenantid
              ORDER BY p.name, r.priority";
        $records = $DB->get_records_sql($sql, ['tenantid' => $tenant->id]);
        $url = new \moodle_url('/local/ai_bridge/route.php', ['tenantid' => $tenant->id]);
        $html = \html_writer::link($url, get_string('addroute', 'local_ai_bridge'), ['class' => 'btn btn-primary mb-3']);
        $table = new \html_table();
        $table->head = [get_string('purpose', 'local_ai_bridge'), get_string('airole', 'local_ai_bridge'), get_string('connection', 'local_ai_bridge'), get_string('model', 'local_ai_bridge'), get_string('priority', 'local_ai_bridge'), get_string('actions')];
        foreach ($records as $record) {
            $edit = new \moodle_url('/local/ai_bridge/route.php', ['tenantid' => $tenant->id, 'id' => $record->id]);
            $table->data[] = [format_string($record->purposename), $record->rolename ? format_string($record->rolename) : get_string('anyrole', 'local_ai_bridge'), format_string($record->connectionname), s($record->model), (int)$record->priority, \html_writer::link($edit, get_string('edit'))];
        }
        return $html . \html_writer::table($table);
    }

    /**
     * Method users.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function users(\stdClass $tenant): string {
        global $DB;
        $mode = get_config('local_ai_bridge', 'tenantkey') ?: 'institution_department';
        $profilewhere = '';
        $params = ['tenantid' => $tenant->id];
        if ($mode === 'institution') {
            $profilewhere = ' AND u.institution = :institution';
            $params['institution'] = (string)$tenant->institution;
        } else if ($mode === 'department') {
            $profilewhere = ' AND u.department = :department';
            $params['department'] = (string)$tenant->department;
        } else {
            $profilewhere = ' AND u.institution = :institution AND u.department = :department';
            $params['institution'] = (string)$tenant->institution;
            $params['department'] = (string)$tenant->department;
        }
        $sql = "SELECT u.id, u.firstname, u.lastname, u.email, ctl.enabled, ctl.creditlimit, ctl.creditused, ar.name AS rolename
                  FROM {user} u
             LEFT JOIN {local_ai_bridge_user} ctl ON ctl.userid = u.id AND ctl.tenantid = :tenantid
             LEFT JOIN {local_ai_bridge_role} ar ON ar.id = ctl.roleid
                 WHERE u.deleted = 0 AND u.id > 1{$profilewhere}
              ORDER BY u.lastname, u.firstname";
        $records = $DB->get_records_sql($sql, $params, 0, 200);
        $table = new \html_table();
        $table->head = [get_string('fullname'), get_string('email'), get_string('airole', 'local_ai_bridge'), get_string('enabled', 'local_ai_bridge'), get_string('creditused', 'local_ai_bridge'), get_string('actions')];
        foreach ($records as $record) {
            $edit = new \moodle_url('/local/ai_bridge/user.php', ['tenantid' => $tenant->id, 'userid' => $record->id]);
            $table->data[] = [fullname($record), s($record->email), $record->rolename ? format_string($record->rolename) : '-', $record->enabled === null ? get_string('default') : ($record->enabled ? get_string('yes') : get_string('no')), format_float((float)($record->creditused ?? 0), 4), \html_writer::link($edit, get_string('edit'))];
        }
        return \html_writer::table($table) . \html_writer::tag('p', get_string('usershint', 'local_ai_bridge'), ['class' => 'text-muted']);
    }

    /**
     * Method roles.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function roles(\stdClass $tenant): string {
        global $DB;
        $records = $DB->get_records('local_ai_bridge_role', ['tenantid' => $tenant->id], 'name ASC');
        $html = \html_writer::link(new \moodle_url('/local/ai_bridge/role.php', ['tenantid' => $tenant->id]), get_string('addrole', 'local_ai_bridge'), ['class' => 'btn btn-primary mb-3']);
        $table = new \html_table();
        $table->head = [get_string('name'), get_string('idnumber'), get_string('defaultrole', 'local_ai_bridge'), get_string('enabled', 'local_ai_bridge'), get_string('actions')];
        foreach ($records as $record) {
            $edit = new \moodle_url('/local/ai_bridge/role.php', ['tenantid' => $tenant->id, 'id' => $record->id]);
            $table->data[] = [format_string($record->name), s($record->idnumber), $record->isdefault ? get_string('yes') : get_string('no'), $record->enabled ? get_string('yes') : get_string('no'), \html_writer::link($edit, get_string('edit'))];
        }
        return $html . \html_writer::table($table);
    }

    /**
     * Method admins.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function admins(\stdClass $tenant): string {
        global $DB, $USER;
        $context = \context_system::instance();
        if (!has_capability('local/ai_bridge:managetenants', $context, (int)$USER->id)) {
            return \html_writer::tag('p', get_string('adminsrestricted', 'local_ai_bridge'));
        }
        $sql = "SELECT u.id, u.firstname, u.lastname, u.email
                  FROM {local_ai_bridge_tenant_admin} a
                  JOIN {user} u ON u.id = a.userid
                 WHERE a.tenantid = :tenantid
              ORDER BY u.lastname, u.firstname";
        $records = $DB->get_records_sql($sql, ['tenantid' => $tenant->id]);
        $html = \html_writer::link(new \moodle_url('/local/ai_bridge/tenant_admin.php', ['tenantid' => $tenant->id]), get_string('addtenantadmin', 'local_ai_bridge'), ['class' => 'btn btn-primary mb-3']);
        $table = new \html_table();
        $table->head = [get_string('fullname'), get_string('email'), get_string('actions')];
        foreach ($records as $record) {
            $remove = new \moodle_url('/local/ai_bridge/tenant_admin.php', ['tenantid' => $tenant->id, 'remove' => $record->id, 'sesskey' => sesskey()]);
            $table->data[] = [fullname($record), s($record->email), \html_writer::link($remove, get_string('remove'))];
        }
        return $html . \html_writer::table($table);
    }

    /**
     * Method credits.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function credits(\stdClass $tenant): string {
        global $DB;
        $url = new \moodle_url('/local/ai_bridge/credits.php', ['tenantid' => $tenant->id]);
        $html = \html_writer::tag('p', get_string('creditbalance', 'local_ai_bridge', format_float((float)$tenant->creditbalance, 4)));
        $html .= \html_writer::link($url, get_string('adjustcredits', 'local_ai_bridge'), ['class' => 'btn btn-primary mb-3']);
        $records = $DB->get_records('local_ai_bridge_credit', ['tenantid' => $tenant->id], 'timecreated DESC', '*', 0, 100);
        $table = new \html_table();
        $table->head = [get_string('date'), get_string('type', 'local_ai_bridge'), get_string('amount', 'local_ai_bridge'), get_string('note', 'local_ai_bridge')];
        foreach ($records as $record) {
            $table->data[] = [userdate($record->timecreated), s($record->type), format_float((float)$record->amount, 4), s((string)$record->note)];
        }
        return $html . \html_writer::table($table);
    }

    /**
     * Method stats.
     *
     * @param \stdClass $tenant Parameter tenant.
     * @return string Return value.
     */
    public static function stats(\stdClass $tenant): string {
        global $DB, $USER;
        $context = \context_system::instance();
        $since = time() - 30 * DAYSECS;
        $sql = "SELECT status, COUNT(1) AS requests, SUM(inputtokens) AS inputtokens,
                       SUM(outputtokens) AS outputtokens, SUM(credits) AS credits,
                       SUM(estimatedcost) AS estimatedcost
                  FROM {local_ai_bridge_usage}
                 WHERE tenantid = :tenantid AND timecreated >= :since
              GROUP BY status";
        $rows = $DB->get_records_sql($sql, ['tenantid' => $tenant->id, 'since' => $since]);
        $table = new \html_table();
        $table->head = [get_string('status'), get_string('requests', 'local_ai_bridge'), get_string('inputtokens', 'local_ai_bridge'), get_string('outputtokens', 'local_ai_bridge'), get_string('credits', 'local_ai_bridge'), get_string('estimatedcost', 'local_ai_bridge')];
        foreach ($rows as $row) {
            $table->data[] = [s($row->status), (int)$row->requests, (int)$row->inputtokens, (int)$row->outputtokens, format_float((float)$row->credits, 4), format_float((float)$row->estimatedcost, 6)];
        }
        $html = \html_writer::table($table);
        if (has_capability('local/ai_bridge:viewdetailedstats', $context, (int)$USER->id)) {
            $sql = "SELECT bridge, model, COUNT(1) AS requests, SUM(totaltokens) AS tokens
                      FROM {local_ai_bridge_usage}
                     WHERE tenantid = :tenantid AND timecreated >= :since
                  GROUP BY bridge, model
                  ORDER BY requests DESC";
            $details = $DB->get_records_sql($sql, ['tenantid' => $tenant->id, 'since' => $since]);
            $detailtable = new \html_table();
            $detailtable->head = [get_string('provider', 'local_ai_bridge'), get_string('model', 'local_ai_bridge'), get_string('requests', 'local_ai_bridge'), get_string('totaltokens', 'local_ai_bridge')];
            foreach ($details as $detail) {
                $detailtable->data[] = [s($detail->bridge), s($detail->model), (int)$detail->requests, (int)$detail->tokens];
            }
            $html .= \html_writer::tag('h3', get_string('detailedstats', 'local_ai_bridge')) . \html_writer::table($detailtable);
        }
        if (has_capability('local/ai_bridge:viewuserstats', $context, (int)$USER->id)) {
            $sql = "SELECT u.id, u.firstname, u.lastname, u.email, COUNT(l.id) AS requests,
                           SUM(l.totaltokens) AS tokens, SUM(l.credits) AS credits
                      FROM {local_ai_bridge_usage} l
                      JOIN {user} u ON u.id = l.userid
                     WHERE l.tenantid = :tenantid AND l.timecreated >= :since
                  GROUP BY u.id, u.firstname, u.lastname, u.email
                  ORDER BY requests DESC, u.lastname, u.firstname";
            $users = $DB->get_records_sql($sql, ['tenantid' => $tenant->id, 'since' => $since]);
            $usertable = new \html_table();
            $usertable->head = [get_string('fullname'), get_string('email'), get_string('requests', 'local_ai_bridge'),
                get_string('totaltokens', 'local_ai_bridge'), get_string('credits', 'local_ai_bridge')];
            foreach ($users as $user) {
                $usertable->data[] = [fullname($user), s($user->email), (int)$user->requests,
                    (int)$user->tokens, format_float((float)$user->credits, 4)];
            }
            $html .= \html_writer::tag('h3', get_string('userstats', 'local_ai_bridge')) . \html_writer::table($usertable);
        }
        return $html;
    }
}
