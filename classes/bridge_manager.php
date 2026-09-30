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
 * bridge_manager.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_bridge;

use core\encryption;
use core_component;
use local_ai_bridge\bridge\provider_interface;
use moodle_exception;
use Throwable;

/**
 * Class bridge_manager.
 */
final class bridge_manager {
    /**
     * Method get_bridges.
     *
     * @return array Return value.
     */
    public static function get_bridges(): array {
        $bridges = [];
        foreach (core_component::get_plugin_list('aibridge') as $name => $path) {
            $class = '\\aibridge_' . $name . '\\bridge';
            if (class_exists($class)) {
                $bridge = new $class();
                if ($bridge instanceof provider_interface) {
                    $bridges[$name] = $bridge;
                }
            }
        }
        return $bridges;
    }

    /**
     * Method get_bridge.
     *
     * @param string $name Parameter name.
     * @return provider_interface Return value.
     */
    public static function get_bridge(string $name): provider_interface {
        $bridges = self::get_bridges();
        if (!isset($bridges[$name])) {
            throw new moodle_exception('error:bridgeunavailable', 'local_ai_bridge', '', $name);
        }
        return $bridges[$name];
    }

    /**
     * Method encrypt_config.
     *
     * @param array $config Parameter config.
     * @return string Return value.
     */
    public static function encrypt_config(array $config): string {
        $json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        return encryption::encrypt($json);
    }

    /**
     * Method decrypt_config.
     *
     * @param string $encrypted Parameter encrypted.
     * @return array Return value.
     */
    public static function decrypt_config(string $encrypted): array {
        try {
            $json = encryption::decrypt($encrypted);
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (Throwable $e) {
            throw new moodle_exception('error:configdecrypt', 'local_ai_bridge');
        }
    }
}
