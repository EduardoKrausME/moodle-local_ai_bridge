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
 * role_form.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();
require_once("{$CFG->libdir}/formslib.php");

/**
 * Class role_form.
 */
final class role_form extends moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
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
