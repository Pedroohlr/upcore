<?php

declare(strict_types=1);

namespace UpCore\Modules\Revisions;

use UpCore\Module;
use WP_Post;

final class RevisionsModule extends Module
{
    private const DEFAULT_LIMIT = 10;

    public function slug(): string
    {
        return 'revisions';
    }

    public function label(): string
    {
        return __('Limite de revisoes', 'upcore');
    }

    public function description(): string
    {
        return __('Limita o numero de revisoes salvas por conteudo para evitar acumulo excessivo no banco de dados.', 'upcore');
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
        add_filter('wp_revisions_to_keep', [$this, 'limit_revisions'], 10, 2);
    }

    public function limit_revisions(int $num, WP_Post $post): int
    {
        return self::DEFAULT_LIMIT;
    }
}
