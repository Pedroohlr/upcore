<?php

declare(strict_types=1);

namespace UpCore\Modules\Cron;

use UpCore\Module;

final class CronModule extends Module
{
    public function slug(): string
    {
        return 'cron';
    }

    public function label(): string
    {
        return __('WP-Cron', 'upcore');
    }

    public function description(): string
    {
        return __('Desativa o pseudo-cron disparado por visita e assume um cron de sistema real configurado na hospedagem.', 'upcore');
    }

    public function category(): string
    {
        return 'performance';
    }

    public function status(): string
    {
        return self::STATUS_PLANNED;
    }

    public function register(): void
    {
        // TODO(upcore): define('DISABLE_WP_CRON', true) + documentar cron de
        // sistema necessario no checklist de deploy.
    }
}
