<?php
namespace local_ai_bridge\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

final class purpose_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'tenantid');
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setType('tenantid', PARAM_INT);
        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('text', 'idnumber', get_string('idnumber'));
        $mform->setType('idnumber', PARAM_ALPHANUMEXT);
        $mform->addRule('idnumber', null, 'required', null, 'client');
        $mform->addElement('textarea', 'systeminstruction', get_string('systeminstruction', 'local_ai_bridge'), ['rows' => 8]);
        $mform->setType('systeminstruction', PARAM_RAW);
        $mform->addElement('text', 'temperature', get_string('temperature', 'local_ai_bridge'));
        $mform->setType('temperature', PARAM_FLOAT);
        $mform->addElement('text', 'maxoutputtokens', get_string('maxoutputtokens', 'local_ai_bridge'));
        $mform->setType('maxoutputtokens', PARAM_INT);
        $mform->addElement('text', 'creditcost', get_string('creditcost', 'local_ai_bridge'));
        $mform->setType('creditcost', PARAM_FLOAT);
        $mform->setDefault('creditcost', 1);
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_ai_bridge'));
        $mform->setDefault('enabled', 1);
        $this->add_action_buttons(true);
    }
}
