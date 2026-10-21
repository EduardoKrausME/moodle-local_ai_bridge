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
 * request_options.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\security;

/**
 * Permit only non-privileged tuning parameters supplied by consuming plugins.
 */
class request_options {
    /**
     * Filter raw options per provider; request identity, messages and configured limits are immutable.
     *
     * @param string $provider Provider identifier.
     * @param array $options Requested options.
     * @return array Filtered options.
     */
    public static function filter(string $provider, array $options): array {
        $scalars = [
            "openai" => ["top_p", "presence_penalty", "frequency_penalty"],
            "claude" => ["top_p", "top_k"],
        ];
        if (isset($scalars[$provider])) {
            return array_filter($options, static function($value, $name) use ($scalars, $provider): bool {
                return in_array($name, $scalars[$provider], true) && is_numeric($value);
            }, ARRAY_FILTER_USE_BOTH);
        }
        if ($provider === "gemini") {
            $generation = $options["generationConfig"] ?? [];
            if (!is_array($generation)) {
                return [];
            }
            $safe = array_intersect_key($generation, array_flip(["topP", "topK"]));
            $safe = array_filter($safe, "is_numeric");
            return $safe ? ["generationConfig" => $safe] : [];
        }
        if ($provider === "ollama") {
            $raw = $options["options"] ?? [];
            if (!is_array($raw)) {
                return [];
            }
            $safe = array_intersect_key($raw, array_flip(["top_k", "top_p", "seed", "repeat_penalty"]));
            $safe = array_filter($safe, "is_numeric");
            return $safe ? ["options" => $safe] : [];
        }
        return [];
    }
}
