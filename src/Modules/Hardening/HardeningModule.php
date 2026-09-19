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

    public function register(): void
    {
        if (! defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }

        add_action('admin_init', [$this, 'protect_uploads_directory']);

        remove_action('wp_head', 'wp_generator');
        add_filter('the_generator', '__return_empty_string');
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
