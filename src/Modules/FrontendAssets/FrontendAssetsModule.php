<?php

declare(strict_types=1);

namespace UpCore\Modules\FrontendAssets;

use UpCore\Module;

final class FrontendAssetsModule extends Module
{
    public function slug(): string
    {
        return 'frontend-assets';
    }

    public function label(): string
    {
        return __('Assets do core no front-end', 'upcore');
    }

    public function description(): string
    {
        return __('Remove CSS/JS do core desnecessarios no front (block-library, jquery-migrate, wp-embed) quando o tema nao depende deles.', 'upcore');
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
        // TODO(upcore): dequeue condicional via wp_enqueue_scripts, nunca global
        // sem checagem (WooCommerce e outros plugins podem depender de jQuery).
    }
}
