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
        return __('Desativa o pseudo-cron disparado por visita (DISABLE_WP_CRON). So habilite depois de configurar um cron de sistema real batendo em wp-cron.php, senao os agendamentos param de rodar.', 'upcore');
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
        if (! defined('DISABLE_WP_CRON')) {
            define('DISABLE_WP_CRON', true);
        }
    }
}
