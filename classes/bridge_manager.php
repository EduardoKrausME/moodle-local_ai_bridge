<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

use local_ai_bridge\local\bridge\provider_interface;

final class bridge_manager {
    public static function get_bridges(): array {
        $bridges = [];
        foreach (\core_component::get_plugin_list('aibridge') as $name => $path) {
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

    public static function get_bridge(string $name): provider_interface {
        $bridges = self::get_bridges();
        if (!isset($bridges[$name])) {
            throw new \moodle_exception('error:bridgeunavailable', 'local_ai_bridge', '', $name);
        }
        return $bridges[$name];
    }

    public static function encrypt_config(array $config): string {
        $json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        return \core\encryption::encrypt($json);
    }

    public static function decrypt_config(string $encrypted): array {
        try {
            $json = \core\encryption::decrypt($encrypted);
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $e) {
            throw new \moodle_exception('error:configdecrypt', 'local_ai_bridge');
        }
    }
}
