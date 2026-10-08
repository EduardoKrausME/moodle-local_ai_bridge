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
 * url_guard.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge\security;

/**
 * Class url_guard.
 */
class url_guard {
    /**
     * Validate the syntax and scheme of a provider endpoint.
     *
     * Connection URLs are managed by tenant administrators and are not restricted to a
     * site-wide host allowlist. Network egress restrictions should be enforced on the server.
     *
     * @param string $url Provider endpoint.
     * @return ?string Validation error, or null when valid.
     */
    public static function validate(string $url): ?string {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return get_string('error:invalidendpoint', 'local_ai_bridge');
        }

        $scheme = strtolower((string)$parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true) ||
                isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            return get_string('error:invalidendpoint', 'local_ai_bridge');
        }

        return null;
    }
}
