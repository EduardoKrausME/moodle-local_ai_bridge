<?php
namespace local_ai_bridge\local\bridge;

defined('MOODLE_INTERNAL') || die();

/**
 * Contract implemented by every AI bridge subplugin.
 *
 * Provider-specific configuration, HTTP requests, response parsing, token accounting and
 * cost calculation belong in the subplugin, not in local_ai_bridge.
 */
interface provider_interface {
    public function get_component(): string;
    public function get_name(): string;
    public function get_description(): string;
    public function add_config_form_elements(\MoodleQuickForm $mform, string $prefix): void;
    public function get_default_config(): array;
    public function get_secret_fields(): array;
    public function validate_config(array $config): array;
    public function generate(request $request, array $config, string $model): response;
}
