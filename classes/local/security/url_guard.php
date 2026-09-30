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

namespace local_ai_bridge\local\security;

/**
 * Class url_guard.
 */
final class url_guard {
    /**
     * Method validate.
     *
     * @param string $url Parameter url.
     * @return ?string Return value.
     */
    public static function validate(string $url): ?string {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return get_string('error:invalidendpoint', 'local_ai_bridge');
        }
        $scheme = strtolower((string)$parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return get_string('error:invalidendpoint', 'local_ai_bridge');
        }
        $host = strtolower(rtrim((string)$parts['host'], '.'));
        $port = isset($parts['port']) ? (int)$parts['port'] : ($scheme === 'https' ? 443 : 80);
        $explicitport = isset($parts['port']);

        foreach (self::allowed_endpoints() as $allowed) {
            if (!self::host_matches($host, $allowed['host'], $allowed['wildcard'])) {
                continue;
            }
            if ($allowed['port'] !== null && $allowed['port'] !== $port) {
                continue;
            }
            if ($allowed['port'] === null && $explicitport && !in_array($port, [80, 443], true)) {
                continue;
            }
            return null;
        }

        $display = $explicitport ? $host . ':' . $port : $host;
        return get_string('error:hostnotallowed', 'local_ai_bridge', $display);
    }

    /**
     * @return array<int, array{host:string, port:?int, wildcard:bool}>
     */
    public static function allowed_endpoints(): array {
        $raw = (string)get_config('local_ai_bridge', 'allowedhosts');
        $entries = preg_split('/[\r\n,]+/', $raw) ?: [];
        $result = [];

        foreach ($entries as $entry) {
            $entry = strtolower(trim($entry));
            if ($entry === '') {
                continue;
            }
            $wildcard = str_starts_with($entry, '*.');
            if ($wildcard) {
                $entry = substr($entry, 2);
            }

            $host = $entry;
            $port = null;
            if (preg_match('/^(.+):(\d{1,5})$/', $entry, $matches)) {
                $host = $matches[1];
                $port = (int)$matches[2];
                if ($port < 1 || $port > 65535) {
                    continue;
                }
            }
            $host = rtrim(trim($host, '[]'), '.');
            if ($host === '') {
                continue;
            }
            $result[] = ['host' => $host, 'port' => $port, 'wildcard' => $wildcard];
        }

        return $result;
    }

    /**
     * Method host_matches.
     *
     * @param string $host Parameter host.
     * @param string $allowed Parameter allowed.
     * @param bool $wildcard Parameter wildcard.
     * @return bool Return value.
     */
    private static function host_matches(string $host, string $allowed, bool $wildcard): bool {
        if (!$wildcard) {
            return $host === $allowed;
        }
        return $host !== $allowed && str_ends_with($host, '.' . $allowed);
    }
}
