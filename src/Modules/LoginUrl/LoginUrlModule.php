<?php

declare(strict_types=1);

namespace UpCore\Modules\LoginUrl;

use UpCore\Module;

final class LoginUrlModule extends Module
{
    public function slug(): string
    {
        return 'login-url';
    }

    public function label(): string
    {
        return __('URL de login personalizada', 'upcore');
    }

    public function description(): string
    {
        return __('Substitui /wp-login.php por uma URL personalizada (ex: /gerenciar) e bloqueia o acesso direto ao endpoint padrao.', 'upcore');
    }

    public function category(): string
    {
        return 'security';
    }

    public function status(): string
    {
        return self::STATUS_PLANNED;
    }

    public function register(): void
    {
        // TODO(upcore): interceptar em plugins_loaded/init, reescrever login_url,
        // lostpassword_url, register_url e logout_url. Exige QA manual completo
        // antes de virar padrao (risco de travar acesso).
    }
}
