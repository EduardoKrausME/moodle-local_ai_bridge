<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Add AI Bridge navigation for users who can manage their tenant.
 *
 * @param global_navigation $navigation
 */
function local_ai_bridge_extend_navigation(global_navigation $navigation): void {
    global $USER;
    if (!isloggedin() || isguestuser()) {
        return;
    }
    $context = context_system::instance();
    if (has_capability('local/ai_bridge:manageall', $context) ||
            \local_ai_bridge\access::has_any_tenant_admin_assignment((int)$USER->id)) {
        $navigation->add(
            get_string('pluginname', 'local_ai_bridge'),
            new moodle_url('/local/ai_bridge/manage.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_ai_bridge'
        );
    }
}
