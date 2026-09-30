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
 * credit_form.php
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
 * Class credit_form.
 */
class credit_form extends moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
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
