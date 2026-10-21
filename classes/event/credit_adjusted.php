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
 * credit_adjusted.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\event;

/**
 * Audit tenant credit balance changes without logging credentials or prompts.
 */
class credit_adjusted extends \core\event\base {
    /**
     * Configure core Moodle event metadata.
     *
     * @return void
     */
    protected function init(): void {
        $this->data["crud"] = "u";
        $this->data["edulevel"] = self::LEVEL_OTHER;
        $this->data["objecttable"] = "local_ai_bridge_credit";
    }

    /**
     * Return the localized audit event name.
     *
     * @return string Localized event name.
     */
    public static function get_name(): string {
        return get_string("event:creditadjusted", "local_ai_bridge");
    }

    /**
     * Describe the administrative change for the Moodle audit log.
     *
     * @return string Audit description with actor and object identifiers.
     */
    public function get_description(): string {
        return "Tenant credit balance changed by user " . $this->userid . " (record " . $this->objectid . ").";
    }
}
