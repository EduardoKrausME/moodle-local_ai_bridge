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
 * Validate provider URLs configured by delegated tenant administrators.
 */
class url_guard {
    /**
     * Hosted providers use pinned public hostnames unless a site administrator opts out.
     * Private network destinations require a separate site-wide opt-in.
     *
     * @param string $url Provider base URL.
     * @param string $provider Provider identifier, or empty for generic public endpoints.
     * @return ?string Validation error.
     */
    public static function validate(string $url, string $provider = ""): ?string {
        $parts = parse_url($url);
        $invalid = get_string("error:invalidendpoint", "local_ai_bridge");
        if ($parts === false || empty($parts["scheme"]) || empty($parts["host"]) ||
                isset($parts["user"]) || isset($parts["pass"]) || isset($parts["fragment"]) ||
                isset($parts["query"])) {
            return $invalid;
        }
        $scheme = strtolower((string)$parts["scheme"]);
        $host = strtolower(rtrim((string)$parts["host"], "."));
        if (!in_array($scheme, ["http", "https"], true) || $host === "" ||
                preg_match("/[\\s\\x00-\\x1f\\x7f]/", $url)) {
            return $invalid;
        }
        if ($scheme !== "https" && $provider !== "ollama") {
            return $invalid;
        }

        $known = [
            "openai" => ["api.openai.com"],
            "gemini" => ["generativelanguage.googleapis.com"],
            "claude" => ["api.anthropic.com"],
        ];
        $pinned = isset($known[$provider]) && in_array($host, $known[$provider], true);
        $customallowed = (bool)get_config("local_ai_bridge", "allowcustomhosts");
        if (isset($known[$provider]) && !$pinned && !$customallowed) {
            return $invalid;
        }
        if ($pinned && isset($parts["port"]) && (int)$parts["port"] !== 443 && !$customallowed) {
            return $invalid;
        }

        if (!$pinned) {
            $hostname = trim($host, "[]");
            $privateallowed = (bool)get_config("local_ai_bridge", "allowprivatehosts");
            if (self::is_private_host($hostname) && !$privateallowed) {
                return $invalid;
            }
            // Custom DNS records can point into internal networks even when their names look public.
            if (filter_var($hostname, FILTER_VALIDATE_IP) === false) {
                if (!preg_match("/^(?=.{1,253}$)[a-z0-9][a-z0-9.-]*[a-z0-9]$/", $hostname) ||
                        str_contains($hostname, "..")) {
                    return $invalid;
                }
                $addresses = gethostbynamel($hostname) ?: [];
                if (function_exists("dns_get_record")) {
                    foreach (dns_get_record($hostname, DNS_AAAA) ?: [] as $answer) {
                        if (isset($answer["ipv6"])) {
                            $addresses[] = $answer["ipv6"];
                        }
                    }
                }
                if (!$addresses) {
                    return $invalid;
                }
                foreach ($addresses as $address) {
                    if (self::is_private_host($address) && !$privateallowed) {
                        return $invalid;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Determine whether an address is a private, loopback, link-local or special-use endpoint.
     *
     * @param string $host Address or hostname.
     * @return bool Whether the destination must be blocked by default.
     */
    public static function is_private_host(string $host): bool {
        $host = strtolower(rtrim(trim($host, "[]"), "."));
        if ($host === "localhost" || str_ends_with($host, ".localhost") ||
                str_ends_with($host, ".local") || str_ends_with($host, ".internal")) {
            return true;
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return filter_var($host, FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if (str_starts_with($host, "::ffff:")) {
                return self::is_private_host(substr($host, 7));
            }
            return filter_var($host, FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }
        return false;
    }
}
