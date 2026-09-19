<?php

declare(strict_types=1);

namespace UpCore\Modules\CoreCleanup;

use UpCore\Module;

final class CoreCleanupModule extends Module
{
    public function slug(): string
    {
        return 'core-cleanup';
    }

    public function label(): string
    {
        return __('Limpeza de recursos do core', 'upcore');
    }

    public function description(): string
    {
        return __('Remove emojis, dashicons para visitantes, oEmbed e metadados desnecessarios do head quando nao utilizados pelo projeto.', 'upcore');
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
        // TODO(upcore): sub-flags independentes por recurso (emoji, dashicons,
        // oembed, head cleanup) em vez de um unico toggle on/off.
    }
}
