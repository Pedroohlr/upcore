<?php

declare(strict_types=1);

namespace UpCore\Modules\XmlRpc;

use UpCore\Module;

final class XmlRpcModule extends Module
{
    public function slug(): string
    {
        return 'xmlrpc';
    }

    public function label(): string
    {
        return __('XML-RPC', 'upcore');
    }

    public function description(): string
    {
        return __('Bloqueia o xmlrpc.php por padrao, com opcao de reativar por projeto.', 'upcore');
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
        // TODO(upcore): interceptar em plugins_loaded/init verificando XMLRPC_REQUEST
        // e responder 403 antes do bootstrap completo do WP.
    }
}
