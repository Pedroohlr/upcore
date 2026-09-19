<?php

declare(strict_types=1);

namespace UpCore\Modules\FrontendAssets;

use UpCore\Module;
use WP_Scripts;

final class FrontendAssetsModule extends Module
{
    public function slug(): string
    {
        return 'frontend-assets';
    }

    public function label(): string
    {
        return __('Assets do core no front-end', 'upcore');
    }

    public function description(): string
    {
        return __('Remove jquery-migrate da dependencia do jQuery no front-end. Outras otimizacoes (block-library, code splitting) ficam fora do MU-plugin: dependem do build do tema.', 'upcore');
    }

    public function category(): string
    {
        return 'performance';
    }

    public function status(): string
    {
        return self::STATUS_READY;
    }

    public function register(): void
    {
        add_action('wp_default_scripts', [$this, 'remove_jquery_migrate']);
    }

    public function remove_jquery_migrate(WP_Scripts $scripts): void
    {
        if (is_admin() || ! isset($scripts->registered['jquery'])) {
            return;
        }

        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            ['jquery-migrate']
        );
    }
}
