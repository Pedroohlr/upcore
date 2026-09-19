<?php

declare(strict_types=1);

namespace UpCore\Rest;

use UpCore\Module;
use UpCore\ModuleManager;
use UpCore\Settings;
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
                    'enabled' => [
                        'required' => true,
                        'type' => 'boolean',
                    ],
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

        $enabled = (bool) $request->get_param('enabled');

        if ($enabled && $module->status() !== Module::STATUS_READY) {
            return new WP_Error(
                'upcore_module_not_ready',
                __('Este modulo ainda nao foi implementado.', 'upcore'),
                ['status' => 400]
            );
        }

        $this->settings->set_enabled($slug, $enabled);

        return new WP_REST_Response($this->to_array($module));
    }

    /** @return array<string, mixed> */
    private function to_array(Module $module): array
    {
        return [
            'slug' => $module->slug(),
            'label' => $module->label(),
            'description' => $module->description(),
            'category' => $module->category(),
            'status' => $module->status(),
            'enabled' => $this->settings->is_enabled($module->slug()),
        ];
    }
}
