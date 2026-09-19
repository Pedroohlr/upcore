<?php

declare(strict_types=1);

namespace UpCore\Modules\XmlRpc;

use UpCore\Module;
use UpCore\Stats;

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
        return __('Bloqueia o xmlrpc.php por padrao. Pode ser reativado por projeto definindo a constante UPCORE_XMLRPC_ALLOWED como true.', 'upcore');
    }

    public function category(): string
    {
        return 'security';
    }

    public function status(): string
    {
        return self::STATUS_READY;
    }

    public function metrics(): array
    {
        return [
            'blocked' => __('Requisicoes XML-RPC bloqueadas', 'upcore'),
        ];
    }

    public function register(): void
    {
        add_action('init', [$this, 'maybe_block_request'], 0);

        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');

        add_filter('wp_headers', [$this, 'remove_pingback_header']);
    }

    public function maybe_block_request(): void
    {
        if ($this->is_allowed_by_override()) {
            return;
        }

        if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
            Stats::record($this->slug(), 'blocked');
            status_header(403);
            nocache_headers();
            exit;
        }
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    public function remove_pingback_header(array $headers): array
    {
        unset($headers['X-Pingback']);

        return $headers;
    }

    private function is_allowed_by_override(): bool
    {
        return defined('UPCORE_XMLRPC_ALLOWED') && UPCORE_XMLRPC_ALLOWED === true;
    }
}
