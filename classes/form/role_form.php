<?php
namespace local_ai_bridge\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

final class role_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'tenantid');
        $mform->setType('tenantid', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('text', 'idnumber', get_string('idnumber'));
        $mform->setType('idnumber', PARAM_ALPHANUMEXT);
        $mform->addRule('idnumber', null, 'required', null, 'client');
        $mform->addElement('advcheckbox', 'isdefault', get_string('defaultrole', 'local_ai_bridge'));
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_ai_bridge'));
        $mform->setDefault('enabled', 1);
        $this->add_action_buttons(true);
    }
}
