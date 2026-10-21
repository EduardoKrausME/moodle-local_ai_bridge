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
 * provider_error.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\security;

/**
 * Sanitize untrusted provider error content before a Moodle exception renders it.
 */
class provider_error {
    /**
     * Convert an untrusted provider response into a bounded plain-text error.
     *
     * @param mixed $message Provider error payload.
     * @return string Safe, bounded plain-text error.
     */
    public static function safe(mixed $message): string {
        if (!is_scalar($message)) {
            return "Provider request failed";
        }
        $text = clean_param((string)$message, PARAM_TEXT);
        return mb_substr(trim(preg_replace("/\\s+/u", " ", $text) ?? ""), 0, 300);
    }
}
