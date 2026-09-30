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
 * request.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\bridge;

/**
 * Class request.
 */
class request {
    /**
     * Method __construct.
     *
     * @param array $messages Parameter messages.
     * @param ?float $temperature Parameter temperature.
     * @param ?int $maxoutputtokens Parameter maxoutputtokens.
     * @param array $options Parameter options.
     */
    public function __construct(
        /** @var array Messages. */
        public readonly array $messages,
        /** @var ?float Temperature. */
        public readonly ?float $temperature = null,
        /** @var ?int Maximum output tokens. */
        public readonly ?int $maxoutputtokens = null,
        /** @var array Additional options. */
        public readonly array $options = [],
    ) {
    }

    /**
     * Method with_system_instruction.
     *
     * @param string $instruction Parameter instruction.
     * @return self Return value.
     */
    public function with_system_instruction(string $instruction): self {
        if (trim($instruction) === '') {
            return $this;
        }
        $messages = $this->messages;
        array_unshift($messages, ['role' => 'system', 'content' => $instruction]);
        return new self($messages, $this->temperature, $this->maxoutputtokens, $this->options);
    }
}
