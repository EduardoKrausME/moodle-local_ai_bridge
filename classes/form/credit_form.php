<?php
namespace local_ai_bridge\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

final class credit_form extends \moodleform {
    protected function definition(): void {
        $mform = $this->_form;
        $mform->addElement('hidden', 'tenantid');
        $mform->setType('tenantid', PARAM_INT);
        $mform->addElement('text', 'amount', get_string('creditamount', 'local_ai_bridge'));
        $mform->setType('amount', PARAM_FLOAT);
        $mform->addRule('amount', null, 'required', null, 'client');
        $mform->addElement('text', 'note', get_string('note', 'local_ai_bridge'));
        $mform->setType('note', PARAM_TEXT);
        $this->add_action_buttons(true, get_string('adjustcredits', 'local_ai_bridge'));
    }
}
