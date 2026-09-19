<?php

declare(strict_types=1);

namespace UpCore\Modules\Heartbeat;

use UpCore\Module;

final class HeartbeatModule extends Module
{
    public function slug(): string
    {
        return 'heartbeat';
    }

    public function label(): string
    {
        return __('Heartbeat API', 'upcore');
    }

    public function description(): string
    {
        return __('Reduz a frequencia da Heartbeat API fora do editor de posts, evitando requisicoes excessivas ao admin-ajax.php.', 'upcore');
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
        // TODO(upcore): heartbeat_settings para aumentar intervalo e
        // wp_deregister_script condicional por tela (nunca em post.php/post-new.php).
    }
}
