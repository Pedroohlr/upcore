<?php

declare(strict_types=1);

namespace UpCore\Modules\Hardening;

use UpCore\Module;

final class HardeningModule extends Module
{
    public function slug(): string
    {
        return 'hardening';
    }

    public function label(): string
    {
        return __('Hardening basico', 'upcore');
    }

    public function description(): string
    {
        return __('Desativa o editor de arquivos do painel, bloqueia execucao de PHP em /uploads/ e remove a versao do WordPress exposta no head e no RSS.', 'upcore');
    }

    public function category(): string
    {
        return 'security';
    }

    public function status(): string
    {
        return self::STATUS_READY;
    }

    public function fields(): array
    {
        return [
            'disable_file_edit' => [
                'label' => __('Desativar editor de arquivos do painel', 'upcore'),
                'type' => 'checkbox',
                'default' => true,
            ],
            'block_uploads_php' => [
                'label' => __('Bloquear execucao de PHP em /uploads/', 'upcore'),
                'type' => 'checkbox',
                'default' => true,
            ],
            'hide_wp_version' => [
                'label' => __('Remover versao do WordPress exposta no head e no RSS', 'upcore'),
                'type' => 'checkbox',
                'default' => true,
            ],
        ];
    }

    public function register(): void
    {
        if ($this->field('disable_file_edit') && ! defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }

        if ($this->field('block_uploads_php')) {
            add_action('admin_init', [$this, 'protect_uploads_directory']);
        }

        if ($this->field('hide_wp_version')) {
            remove_action('wp_head', 'wp_generator');
            add_filter('the_generator', '__return_empty_string');
        }
    }

    public function protect_uploads_directory(): void
    {
        $uploads = wp_get_upload_dir();

        if (! empty($uploads['error'])) {
            return;
        }

        $htaccess = trailingslashit($uploads['basedir']) . '.htaccess';

        if (file_exists($htaccess) || ! wp_is_writable($uploads['basedir'])) {
            return;
        }

        $rules = "<FilesMatch \"\\.(?:php|phtml|php\\d)\$\">\n\tRequire all denied\n</FilesMatch>\n";

        file_put_contents($htaccess, $rules);
    }
}
