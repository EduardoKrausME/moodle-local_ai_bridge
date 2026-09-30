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
 * response.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\bridge;

/**
 * Class response.
 */
class response {
    /**
     * Method __construct.
     *
     * @param string $text Parameter text.
     * @param string $model Parameter model.
     * @param int $inputtokens Parameter inputtokens.
     * @param int $outputtokens Parameter outputtokens.
     * @param int $totaltokens Parameter totaltokens.
     * @param float $estimatedcost Parameter estimatedcost.
     * @param array $metadata Parameter metadata.
     */
    public function __construct(
        public readonly string $text,
        public readonly string $model,
        public readonly int    $inputtokens = 0,
        public readonly int    $outputtokens = 0,
        public readonly int    $totaltokens = 0,
        public readonly float  $estimatedcost = 0.0,
        public readonly array  $metadata = [],
    ) {
    }
}
