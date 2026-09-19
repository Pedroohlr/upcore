<?php

declare(strict_types=1);

namespace UpCore\Modules\UserEnumeration;

use UpCore\Module;

final class UserEnumerationModule extends Module
{
    public function slug(): string
    {
        return 'user-enumeration';
    }

    public function label(): string
    {
        return __('Enumeracao de usuarios', 'upcore');
    }

    public function description(): string
    {
        return __('Bloqueia ?author=N e restringe endpoints da REST API que expoe dados de usuarios, sem afetar as rotas usadas pelo front React.', 'upcore');
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
        // TODO(upcore): template_redirect para ?author=, rest_authentication_errors
        // com allowlist para /wp/v2/users/me, e mensagem generica em login_errors.
    }
}
