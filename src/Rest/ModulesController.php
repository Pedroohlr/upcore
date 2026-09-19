<?php

declare(strict_types=1);

namespace UpCore\Rest;

use UpCore\Module;
use UpCore\ModuleManager;
use UpCore\Settings;
use UpCore\Stats;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

final class ModulesController
{
    private const NAMESPACE = 'upcore/v1';

    public function __construct(
        private readonly ModuleManager $modules,
        private readonly Settings $settings
    ) {
    }

    public function register_routes(): void
    {
        register_rest_route(self::NAMESPACE, '/modules', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'list_modules'],
                'permission_callback' => [$this, 'check_permission'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/modules/(?P<slug>[a-z0-9-]+)', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_module'],
                'permission_callback' => [$this, 'check_permission'],
                'args' => [
                    'enabled' => ['type' => 'boolean'],
                    'config' => ['type' => 'object'],
                ],
            ],
        ]);
    }

    public function check_permission(): bool
    {
        return current_user_can('manage_options');
    }

    public function list_modules(): WP_REST_Response
    {
        $data = array_map(
            fn (Module $module): array => $this->to_array($module),
            array_values($this->modules->all())
        );

        return new WP_REST_Response($data);
    }

    /**
     * @return WP_REST_Response|WP_Error
     */
    public function update_module(WP_REST_Request $request)
    {
        $slug = (string) $request->get_param('slug');
        $module = $this->modules->get($slug);

        if (! $module instanceof Module) {
            return new WP_Error(
                'upcore_module_not_found',
                __('Modulo nao encontrado.', 'upcore'),
                ['status' => 404]
            );
        }

        $enabled = $request->get_param('enabled');

        if ($enabled !== null) {
            $enabled = (bool) $enabled;

            if ($enabled && $module->status() !== Module::STATUS_READY) {
                return new WP_Error(
                    'upcore_module_not_ready',
                    __('Este modulo ainda nao foi implementado.', 'upcore'),
                    ['status' => 400]
                );
            }

            $this->settings->set_enabled($slug, $enabled);
        }

        $config = $request->get_param('config');

        if (is_array($config)) {
            $this->settings->set_config($slug, $this->sanitize_config($module, $config));
        }

        return new WP_REST_Response($this->to_array($module));
    }

    /**
     * @param array<string, mixed> $submitted
     * @return array<string, string>
     */
    private function sanitize_config(Module $module, array $submitted): array
    {
        $allowed = array_keys($module->fields());
        $sanitized = [];

        foreach ($submitted as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $sanitized[$key] = sanitize_text_field((string) $value);
            }
        }

        return $sanitized;
    }

    /** @return array<string, mixed> */
    private function to_array(Module $module): array
    {
        $slug = $module->slug();
        $stats = Stats::all()[$slug] ?? [];

        return [
            'slug' => $slug,
            'label' => $module->label(),
            'description' => $module->description(),
            'category' => $module->category(),
            'status' => $module->status(),
            'enabled' => $this->settings->is_enabled($slug),
            'fields' => $this->fields_to_array($module->fields()),
            'config' => $this->settings->get_config($slug),
            'stats' => $this->stats_to_array($module->metrics(), $stats),
        ];
    }

    /**
     * @param array<string, array{label: string, type: string, description?: string}> $fields
     * @return array<int, array<string, string>>
     */
    private function fields_to_array(array $fields): array
    {
        $result = [];

        foreach ($fields as $key => $field) {
            $result[] = [
                'key' => $key,
                'label' => $field['label'] ?? $key,
                'type' => $field['type'] ?? 'text',
                'description' => $field['description'] ?? '',
            ];
        }

        return $result;
    }

    /**
     * @param array<string, string> $metrics
     * @param array<string, int> $values
     * @return array<int, array<string, mixed>>
     */
    private function stats_to_array(array $metrics, array $values): array
    {
        $result = [];

        foreach ($metrics as $key => $label) {
            $result[] = [
                'key' => $key,
                'label' => $label,
                'count' => $values[$key] ?? 0,
            ];
        }

        return $result;
    }
}
