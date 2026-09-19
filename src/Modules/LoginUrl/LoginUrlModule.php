<?php

declare(strict_types=1);

namespace UpCore\Modules\LoginUrl;

use UpCore\Module;

final class LoginUrlModule extends Module
{
    private const DEFAULT_SLUG = 'gerenciar';

    public function slug(): string
    {
        return 'login-url';
    }

    public function label(): string
    {
        return __('URL de login personalizada', 'upcore');
    }

    public function description(): string
    {
        return sprintf(
            /* translators: %s: default login slug */
            __('Substitui /wp-login.php por /%s (configuravel via UPCORE_LOGIN_SLUG) e bloqueia o acesso direto ao endpoint padrao. Em emergencia, defina UPCORE_LOGIN_URL_DISABLED como true no wp-config.php para restaurar o comportamento original.', 'upcore'),
            self::DEFAULT_SLUG
        );
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
        if ($this->is_disabled_by_override()) {
            return;
        }

        // wp_login_url()/wp_logout_url()/wp_registration_url()/wp_lostpassword_url()
        // e ate o action="" do proprio formulario de login passam por site_url()
        // (network_site_url() delega para site_url() fora de multisite), entao
        // um unico filtro cobre todos os links gerados pelo core.
        add_filter('site_url', [$this, 'rewrite_login_url'], 10, 2);
        add_filter('network_site_url', [$this, 'rewrite_login_url'], 10, 2);

        // init, nao plugins_loaded: wp_functionality_constants() (AUTOSAVE_INTERVAL
        // e outras constantes que o proprio wp-login.php usa ao imprimir scripts)
        // so roda depois que a action plugins_loaded termina.
        add_action('init', [$this, 'intercept_request'], 0);
    }

    /**
     * @param mixed $path
     */
    public function rewrite_login_url(string $url, $path): string
    {
        if (! is_string($path) || ! str_starts_with($path, 'wp-login.php')) {
            return $url;
        }

        return str_replace('wp-login.php', $this->login_slug(), $url);
    }

    public function intercept_request(): void
    {
        $path = $this->current_relative_path();

        if ($path === $this->login_slug()) {
            $this->serve_login();

            return;
        }

        if ($path === 'wp-login.php') {
            $this->block_direct_access();
        }
    }

    private function serve_login(): void
    {
        global $pagenow;
        $pagenow = 'wp-login.php';

        require ABSPATH . 'wp-login.php';
        exit;
    }

    private function block_direct_access(): void
    {
        status_header(404);
        nocache_headers();
        wp_die(__('Pagina nao encontrada.', 'upcore'), '', ['response' => 404]);
    }

    private function current_relative_path(): string
    {
        $request_path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $site_path = (string) parse_url(site_url('/'), PHP_URL_PATH);

        if ($site_path !== '/' && str_starts_with($request_path, $site_path)) {
            $request_path = substr($request_path, strlen($site_path));
        }

        return trim($request_path, '/');
    }

    private function login_slug(): string
    {
        if (defined('UPCORE_LOGIN_SLUG') && is_string(UPCORE_LOGIN_SLUG) && UPCORE_LOGIN_SLUG !== '') {
            return trim(UPCORE_LOGIN_SLUG, '/');
        }

        return self::DEFAULT_SLUG;
    }

    private function is_disabled_by_override(): bool
    {
        return defined('UPCORE_LOGIN_URL_DISABLED') && UPCORE_LOGIN_URL_DISABLED === true;
    }
}
