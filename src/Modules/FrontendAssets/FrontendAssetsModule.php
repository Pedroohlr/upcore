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
        return __('Remove assets do core que a maioria dos projetos React nao usa no front-end. Code splitting e lazy loading do proprio tema ficam fora do MU-plugin: dependem do build do tema, nao de hooks do WordPress.', 'upcore');
    }

    public function category(): string
    {
        return 'performance';
    }

    public function status(): string
    {
        return self::STATUS_READY;
    }

    public function fields(): array
    {
        return [
            'remove_jquery_migrate' => [
                'label' => __('Remover jquery-migrate', 'upcore'),
                'type' => 'checkbox',
                'default' => true,
            ],
            'remove_block_library_css' => [
                'label' => __('Remover CSS do block-library', 'upcore'),
                'type' => 'checkbox',
                'description' => __('Cuidado: quebra o estilo dos blocos do Gutenberg se o conteudo do site usar blocos nativos. So ative se o front for 100% React/headless.', 'upcore'),
                'default' => false,
            ],
        ];
    }

    public function register(): void
    {
        if ($this->field('remove_jquery_migrate')) {
            add_action('wp_default_scripts', [$this, 'remove_jquery_migrate']);
        }

        if ($this->field('remove_block_library_css')) {
            add_action('wp_enqueue_scripts', [$this, 'remove_block_library_assets'], 100);
        }
    }

    public function remove_block_library_assets(): void
    {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('classic-theme-styles');
        wp_dequeue_style('global-styles');
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
