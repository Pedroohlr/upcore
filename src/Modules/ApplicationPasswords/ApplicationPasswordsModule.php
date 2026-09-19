<?php

declare(strict_types=1);

namespace UpCore\Modules\ApplicationPasswords;

use UpCore\Module;

final class ApplicationPasswordsModule extends Module
{
    public function slug(): string
    {
        return 'application-passwords';
    }

    public function label(): string
    {
        return __('Application Passwords', 'upcore');
    }

    public function description(): string
    {
        return __('Desativa a criacao de Application Passwords nativas do WordPress.', 'upcore');
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
        add_filter('wp_is_application_passwords_available', '__return_false');
    }
}
