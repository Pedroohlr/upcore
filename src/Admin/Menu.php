<?php

declare(strict_types=1);

namespace UpCore\Admin;

use UpCore\ModuleManager;
use UpCore\Settings;

final class Menu
{
    private const PAGE_SLUG = 'upcore';

    public function __construct(
        private readonly ModuleManager $modules,
        private readonly Settings $settings
    ) {
    }

    public function register(): void
    {
        $hook = add_menu_page(
            __('UpCore', 'upcore'),
            __('UpCore', 'upcore'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_page'],
            $this->menu_icon(),
            80
        );

        add_action('admin_enqueue_scripts', function (string $current_hook) use ($hook): void {
            if ($current_hook === $hook) {
                $this->enqueue_assets();
            }
        });
    }

    public function render_page(): void
    {
        echo '<div id="upcore-admin-root"></div>';
    }

    public function enqueue_assets(): void
    {
        $asset_file = UPCORE_DIR . '/assets/admin-app/build/index.asset.php';

        if (! file_exists($asset_file)) {
            return;
        }

        $asset = require $asset_file;

        wp_enqueue_script(
            'upcore-admin',
            UPCORE_URL . '/assets/admin-app/build/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_enqueue_style(
            'upcore-admin',
            UPCORE_URL . '/assets/admin-app/build/style-index.css',
            [],
            $asset['version']
        );

        wp_localize_script('upcore-admin', 'upcoreAdmin', [
            'restUrl' => esc_url_raw(rest_url('upcore/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
            'logoUrl' => UPCORE_URL . '/assets/logo.svg',
        ]);
    }

    private function menu_icon(): string
    {
        $icon = UPCORE_DIR . '/assets/icon.svg';

        if (! file_exists($icon)) {
            return 'dashicons-shield';
        }

        return 'data:image/svg+xml;base64,' . base64_encode((string) file_get_contents($icon));
    }
}
