<?php

declare(strict_types=1);

namespace UpCore\Modules\LoginThrottle;

use UpCore\Module;

final class LoginThrottleModule extends Module
{
    public function slug(): string
    {
        return 'login-throttle';
    }

    public function label(): string
    {
        return __('Limite de tentativas de login', 'upcore');
    }

    public function description(): string
    {
        return __('Limita tentativas de login e de recuperacao de senha por IP, com bloqueio temporario.', 'upcore');
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
        // TODO(upcore): contador de falhas via wp_login_failed + transient por IP,
        // checagem no filtro authenticate, e mesmo mecanismo no lostpassword_post.
    }
}
