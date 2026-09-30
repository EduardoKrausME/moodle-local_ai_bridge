<?php
namespace local_ai_bridge\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

final class user_form extends \moodleform {
    protected function definition(): void {
        $roles = $this->_customdata['roles'];
        $user = $this->_customdata['user'];
        $mform = $this->_form;
        $mform->addElement('hidden', 'tenantid');
        $mform->setType('tenantid', PARAM_INT);
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('static', 'username', get_string('user'), fullname($user) . ' &lt;' . s($user->email) . '&gt;');
        $mform->addElement('select', 'roleid', get_string('airole', 'local_ai_bridge'), $roles);
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_ai_bridge'));
        $mform->addElement('text', 'creditlimit', get_string('usercreditlimit', 'local_ai_bridge'));
        $mform->setType('creditlimit', PARAM_FLOAT);
        $mform->addHelpButton('creditlimit', 'usercreditlimit', 'local_ai_bridge');
        $this->add_action_buttons(true);
    }
}
