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
 * provider_interface.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\bridge;

use MoodleQuickForm;

/**
 * Contract implemented by every AI bridge subplugin.
 *
 * Provider-specific configuration, HTTP requests, response parsing, token accounting and
 * cost calculation belong in the subplugin, not in local_ai_bridge.
 */
interface provider_interface {
    /**
     * Method get_component.
     *
     * @return string Return value.
     */
    public function get_component(): string;

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public function get_name(): string;

    /**
     * Method get_description.
     *
     * @return string Return value.
     */
    public function get_description(): string;

    /**
     * Method add_config_form_elements.
     *
     * @param MoodleQuickForm $mform Parameter mform.
     * @param string $prefix Parameter prefix.
     * @return void Return value.
     */
    public function add_config_form_elements(MoodleQuickForm $mform, string $prefix): void;

    /**
     * Method get_default_config.
     *
     * @return array Return value.
     */
    public function get_default_config(): array;

    /**
     * Method get_secret_fields.
     *
     * @return array Return value.
     */
    public function get_secret_fields(): array;

    /**
     * Method validate_config.
     *
     * @param array $config Parameter config.
     * @return array Return value.
     */
    public function validate_config(array $config): array;

    /**
     * Method generate.
     *
     * @param request $request Parameter request.
     * @param array $config Parameter config.
     * @param string $model Parameter model.
     * @return response Return value.
     */
    public function generate(request $request, array $config, string $model): response;
}
