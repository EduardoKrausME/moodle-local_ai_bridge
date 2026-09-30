<?php
namespace local_ai_bridge\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

final class route_form extends \moodleform {
    protected function definition(): void {
        global $DB;
        $tenantid = (int)$this->_customdata['tenantid'];
        $mform = $this->_form;
        $mform->addElement('hidden', 'tenantid', $tenantid);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setType('tenantid', PARAM_INT);
        $purposes = $DB->get_records_menu('local_ai_bridge_purpose', ['tenantid' => $tenantid, 'enabled' => 1], 'name', 'id,name');
        $mform->addElement('select', 'purposeid', get_string('purpose', 'local_ai_bridge'), $purposes);
        $roles = ['' => get_string('anyrole', 'local_ai_bridge')] +
            $DB->get_records_menu('local_ai_bridge_role', ['tenantid' => $tenantid, 'enabled' => 1], 'name', 'id,name');
        $mform->addElement('select', 'roleid', get_string('airole', 'local_ai_bridge'), $roles);
        $connections = $DB->get_records_menu('local_ai_bridge_connection', ['tenantid' => $tenantid, 'enabled' => 1], 'name', 'id,name');
        $mform->addElement('select', 'connectionid', get_string('connection', 'local_ai_bridge'), $connections);
        $mform->addElement('text', 'model', get_string('model', 'local_ai_bridge'));
        $mform->setType('model', PARAM_TEXT);
        $mform->addRule('model', null, 'required', null, 'client');
        $mform->addElement('text', 'priority', get_string('priority', 'local_ai_bridge'));
        $mform->setType('priority', PARAM_INT);
        $mform->setDefault('priority', 100);
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_ai_bridge'));
        $mform->setDefault('enabled', 1);
        $this->add_action_buttons(true);
    }
}
