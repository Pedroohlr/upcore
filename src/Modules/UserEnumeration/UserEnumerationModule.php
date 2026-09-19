<?php

declare(strict_types=1);

namespace UpCore\Modules\UserEnumeration;

use UpCore\Module;
use UpCore\Stats;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Request;

final class UserEnumerationModule extends Module
{
    public function slug(): string
    {
        return 'user-enumeration';
    }

    public function label(): string
    {
        return __('Enumeracao de usuarios', 'upcore');
    }

    public function description(): string
    {
        return __('Bloqueia ?author=N e a listagem publica de usuarios na REST API. Nao afeta a busca de um usuario por ID (usada em embeds de autor de post) nem rotas usadas pelo front React.', 'upcore');
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
            'blocked_author_query' => __('Tentativas de ?author=N bloqueadas', 'upcore'),
            'blocked_rest' => __('Listagens de /wp/v2/users bloqueadas', 'upcore'),
        ];
    }

    public function register(): void
    {
        // Prioridade 1: precisa rodar antes de redirect_canonical() (prioridade
        // padrao 10), senao o proprio WP ja revela o slug do usuario no header
        // Location antes do nosso bloqueio ter a chance de agir.
        add_action('template_redirect', [$this, 'block_author_query'], 1);
        add_filter('rest_request_before_callbacks', [$this, 'restrict_users_collection'], 10, 3);
        add_filter('login_errors', [$this, 'generic_login_error']);
    }

    public function block_author_query(): void
    {
        if (empty($_GET['author']) || ! is_author()) {
            return;
        }

        Stats::record($this->slug(), 'blocked_author_query');
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }

    /**
     * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response
     * @param array<string, mixed> $handler
     * @return WP_REST_Response|WP_HTTP_Response|WP_Error|mixed
     */
    public function restrict_users_collection($response, array $handler, WP_REST_Request $request)
    {
        if ($response instanceof WP_Error) {
            return $response;
        }

        if ($request->get_route() !== '/wp/v2/users') {
            return $response;
        }

        if (is_user_logged_in()) {
            return $response;
        }

        Stats::record($this->slug(), 'blocked_rest');

        return new WP_Error(
            'upcore_rest_forbidden',
            __('Nao autorizado.', 'upcore'),
            ['status' => 401]
        );
    }

    public function generic_login_error(string $errors): string
    {
        return '<p>' . esc_html__('Credenciais invalidas.', 'upcore') . '</p>';
    }
}
