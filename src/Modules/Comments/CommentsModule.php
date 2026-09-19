<?php

declare(strict_types=1);

namespace UpCore\Modules\Comments;

use UpCore\Module;
use WP_Admin_Bar;

final class CommentsModule extends Module
{
    public function slug(): string
    {
        return 'comments';
    }

    public function label(): string
    {
        return __('Comentarios', 'upcore');
    }

    public function description(): string
    {
        return __('Desativa comentarios, pingbacks/trackbacks, feed de comentarios, REST e XML-RPC, alem de remover a interface relacionada no painel.', 'upcore');
    }

    public function category(): string
    {
        return 'security';
    }

    public function status(): string
    {
        return self::STATUS_READY;
    }

    public function register(): void
    {
        add_action('admin_init', [$this, 'disable_support'], 100);
        add_action('admin_init', [$this, 'redirect_admin_pages']);
        add_action('admin_menu', [$this, 'remove_admin_menu'], 999);
        add_action('admin_bar_menu', [$this, 'remove_admin_bar_node'], 999);
        add_action('wp_dashboard_setup', [$this, 'remove_dashboard_widget']);
        add_action('widgets_init', [$this, 'unregister_widget'], 20);
        add_action('template_redirect', [$this, 'block_comment_feed']);

        add_filter('comments_open', '__return_false', 20, 2);
        add_filter('pings_open', '__return_false', 20, 2);
        add_filter('comments_array', '__return_empty_array', 10, 2);
        add_filter('rest_endpoints', [$this, 'remove_rest_routes']);
        add_filter('xmlrpc_methods', [$this, 'remove_xmlrpc_methods']);
        add_filter('wp_headers', [$this, 'remove_pingback_header']);
        add_filter('allowed_block_types_all', [$this, 'remove_comment_blocks']);
    }

    public function disable_support(): void
    {
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
            }

            if (post_type_supports($post_type, 'trackbacks')) {
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    public function redirect_admin_pages(): void
    {
        global $pagenow;

        if (in_array($pagenow, ['edit-comments.php', 'options-discussion.php'], true)) {
            wp_safe_redirect(admin_url());
            exit;
        }
    }

    public function remove_admin_menu(): void
    {
        remove_menu_page('edit-comments.php');
    }

    public function remove_admin_bar_node(WP_Admin_Bar $admin_bar): void
    {
        $admin_bar->remove_node('comments');
    }

    public function remove_dashboard_widget(): void
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    public function unregister_widget(): void
    {
        unregister_widget('WP_Widget_Recent_Comments');
    }

    /**
     * @param array<string, mixed> $endpoints
     * @return array<string, mixed>
     */
    public function remove_rest_routes(array $endpoints): array
    {
        foreach (array_keys($endpoints) as $route) {
            if (str_starts_with($route, '/wp/v2/comments')) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }

    /**
     * @param array<string, string> $methods
     * @return array<string, string>
     */
    public function remove_xmlrpc_methods(array $methods): array
    {
        unset(
            $methods['wp.newComment'],
            $methods['wp.getCommentStatusList'],
            $methods['wp.getComment'],
            $methods['wp.getComments'],
            $methods['wp.deleteComment'],
            $methods['wp.editComment'],
            $methods['pingback.ping'],
            $methods['pingback.extensions.getPingbacks']
        );

        return $methods;
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

    public function block_comment_feed(): void
    {
        if (! is_comment_feed()) {
            return;
        }

        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }

    /**
     * @param array<int, string>|bool $allowed_blocks
     * @return array<int, string>|bool
     */
    public function remove_comment_blocks($allowed_blocks)
    {
        if (! is_array($allowed_blocks)) {
            return $allowed_blocks;
        }

        return array_values(array_diff($allowed_blocks, [
            'core/comments',
            'core/comment-template',
            'core/comments-query-loop',
            'core/post-comments-form',
            'core/comments-pagination',
            'core/comments-pagination-next',
            'core/comments-pagination-previous',
            'core/comments-title',
        ]));
    }
}
