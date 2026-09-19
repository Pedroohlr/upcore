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
        return __('Remove emojis, links de descoberta de oEmbed, dashicons para visitantes deslogados e metadados desnecessarios do head.', 'upcore');
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
        $this->remove_emoji();
        $this->remove_oembed_discoverability();
        $this->remove_head_bloat();

        add_action('wp_enqueue_scripts', [$this, 'dequeue_dashicons_for_visitors'], 100);
    }

    private function remove_emoji(): void
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('embed_head', 'print_emoji_detection_script');
        remove_action('wp_enqueue_scripts', 'wp_enqueue_emoji_styles');
        remove_action('enqueue_embed_scripts', 'wp_enqueue_emoji_styles');

        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    }

    /**
     * Remove apenas a metade "disponibilizar este site para embed em outros
     * lugares". Nao mexe no consumo de embeds de terceiros dentro do
     * conteudo (shortcode/bloco de embed continuam funcionando).
     */
    private function remove_oembed_discoverability(): void
    {
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 4);
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
    }

    private function remove_head_bloat(): void
    {
        remove_action('wp_head', 'wp_shortlink_wp_head', 10);
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
    }

    public function dequeue_dashicons_for_visitors(): void
    {
        if (is_admin_bar_showing()) {
            return;
        }

        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}
