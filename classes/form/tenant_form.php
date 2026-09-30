<?php
namespace local_ai_bridge\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

final class tenant_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('text', 'idnumber', get_string('idnumber'));
        $mform->setType('idnumber', PARAM_ALPHANUMEXT);
        $mform->addRule('idnumber', null, 'required', null, 'client');
        $mform->addElement('text', 'institution', get_string('institution'));
        $mform->setType('institution', PARAM_TEXT);
        $mform->addElement('text', 'department', get_string('department'));
        $mform->setType('department', PARAM_TEXT);
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_ai_bridge'));
        $mform->setDefault('enabled', 1);
        $this->add_action_buttons(true);
    }
}
