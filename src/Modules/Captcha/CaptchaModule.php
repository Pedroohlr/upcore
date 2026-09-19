<?php

declare(strict_types=1);

namespace UpCore\Modules\Captcha;

use UpCore\Module;

final class CaptchaModule extends Module
{
    public function slug(): string
    {
        return 'captcha';
    }

    public function label(): string
    {
        return __('CAPTCHA', 'upcore');
    }

    public function description(): string
    {
        return __('Adiciona CAPTCHA no login, recuperacao de senha e cadastro.', 'upcore');
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
        // TODO(upcore): integrar reCAPTCHA v3 via authenticate, lostpassword_post
        // e registration_errors. Precisa de site key/secret key por projeto.
    }
}
