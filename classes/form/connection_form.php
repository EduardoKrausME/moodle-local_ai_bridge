<?php
namespace local_ai_bridge\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

final class connection_form extends \moodleform {
    protected function definition(): void {
        $bridge = $this->_customdata['bridge'];
        $mform = $this->_form;
        $mform->addElement('hidden', 'tenantid');
        $mform->setType('tenantid', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'bridge');
        $mform->setType('bridge', PARAM_ALPHANUMEXT);
        $mform->addElement('text', 'name', get_string('connectionname', 'local_ai_bridge'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addElement('static', 'providername', get_string('provider', 'local_ai_bridge'), $bridge->get_name());
        $mform->addElement('static', 'providerdescription', '', $bridge->get_description());
        $bridge->add_config_form_elements($mform, 'bridgeconfig_');
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_ai_bridge'));
        $mform->setDefault('enabled', 1);
        $this->add_action_buttons(true);
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $bridge = $this->_customdata['bridge'];
        $config = $this->_customdata['existingconfig'] ?? [];
        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'bridgeconfig_')) {
                $name = substr($key, 13);
                if ($value !== '' || !in_array($name, $bridge->get_secret_fields(), true)) {
                    $config[$name] = $value;
                }
            }
        }
        foreach ($bridge->validate_config($config) as $field => $error) {
            $errors['bridgeconfig_' . $field] = $error;
        }
        return $errors;
    }
}
