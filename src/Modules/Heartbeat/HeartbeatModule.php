<?php

declare(strict_types=1);

namespace UpCore\Modules\Heartbeat;

use UpCore\Module;
use WP_Screen;

final class HeartbeatModule extends Module
{
    private const SLOWED_INTERVAL = 60;

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
        return __('Reduz a frequencia da Heartbeat API fora do editor de posts, evitando requisicoes excessivas ao admin-ajax.php. Nao desativa dentro do editor (autosave e bloqueio de edicao continuam normais).', 'upcore');
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
        add_filter('heartbeat_settings', [$this, 'slow_down_outside_editor']);
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function slow_down_outside_editor(array $settings): array
    {
        if ($this->is_post_edit_screen()) {
            return $settings;
        }

        $settings['interval'] = self::SLOWED_INTERVAL;

        return $settings;
    }

    private function is_post_edit_screen(): bool
    {
        if (! function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();

        return $screen instanceof WP_Screen && $screen->base === 'post';
    }
}
