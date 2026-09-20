<?php

declare(strict_types=1);

namespace UpCore\Modules\LoginThrottle;

use UpCore\Module;
use UpCore\Stats;
use WP_Error;

final class LoginThrottleModule extends Module
{
    private const DEFAULT_MAX_LOGIN_ATTEMPTS = 5;
    private const DEFAULT_LOGIN_LOCKOUT_MINUTES = 15;

    private const DEFAULT_MAX_RESET_ATTEMPTS = 3;
    private const DEFAULT_RESET_LOCKOUT_MINUTES = 30;

    public function slug(): string
    {
        return 'login-throttle';
    }

    public function label(): string
    {
        return __('Limite de tentativas de login', 'upcore');
    }

    public function description(): string
    {
        return __('Bloqueia temporariamente um IP apos varias tentativas de login ou de recuperacao de senha. Usa REMOTE_ADDR por padrao; projetos atras de proxy/CDN podem ajustar via filtro upcore_login_throttle_client_ip.', 'upcore');
    }

    public function category(): string
    {
        return 'security';
    }

    public function status(): string
    {
        return self::STATUS_READY;
    }

    public function fields(): array
    {
        return [
            'max_login_attempts' => [
                'label' => __('Max. tentativas de login', 'upcore'),
                'type' => 'number',
                'default' => self::DEFAULT_MAX_LOGIN_ATTEMPTS,
            ],
            'login_lockout_minutes' => [
                'label' => __('Bloqueio de login apos exceder (minutos)', 'upcore'),
                'type' => 'number',
                'default' => self::DEFAULT_LOGIN_LOCKOUT_MINUTES,
            ],
            'max_reset_attempts' => [
                'label' => __('Max. solicitacoes de recuperacao de senha', 'upcore'),
                'type' => 'number',
                'default' => self::DEFAULT_MAX_RESET_ATTEMPTS,
            ],
            'reset_lockout_minutes' => [
                'label' => __('Bloqueio de recuperacao apos exceder (minutos)', 'upcore'),
                'type' => 'number',
                'default' => self::DEFAULT_RESET_LOCKOUT_MINUTES,
            ],
        ];
    }

    public function metrics(): array
    {
        return [
            'blocked_login' => __('Tentativas de login bloqueadas (rate limit)', 'upcore'),
            'blocked_reset' => __('Recuperacoes de senha bloqueadas (rate limit)', 'upcore'),
            'failed_attempts' => __('Tentativas de login com credenciais invalidas', 'upcore'),
        ];
    }

    public function register(): void
    {
        // Prioridade 30: roda depois de wp_authenticate_username_password (20),
        // assim sobrescreve o resultado mesmo quando a senha esta correta.
        add_filter('authenticate', [$this, 'block_login_if_locked_out'], 30);
        add_action('wp_login_failed', [$this, 'register_failed_login']);

        add_action('lostpassword_post', [$this, 'guard_password_reset'], 5, 2);
    }

    /**
     * @param mixed $user
     * @return mixed|WP_Error
     */
    public function block_login_if_locked_out($user)
    {
        if ($this->is_locked_out($this->login_key())) {
            Stats::record($this->slug(), 'blocked_login');

            return new WP_Error(
                'upcore_too_many_attempts',
                __('Muitas tentativas de login. Tente novamente em alguns minutos.', 'upcore')
            );
        }

        return $user;
    }

    public function register_failed_login(string $username): void
    {
        Stats::record($this->slug(), 'failed_attempts');
        $this->register_attempt($this->login_key(), $this->minutes_to_seconds('login_lockout_minutes', self::DEFAULT_LOGIN_LOCKOUT_MINUTES));
    }

    /**
     * @param mixed $user_data
     */
    public function guard_password_reset(WP_Error $errors, $user_data = null): void
    {
        if ($this->is_locked_out($this->reset_key())) {
            Stats::record($this->slug(), 'blocked_reset');

            $errors->add(
                'upcore_too_many_reset_attempts',
                __('Muitas solicitacoes de recuperacao de senha. Tente novamente mais tarde.', 'upcore')
            );

            return;
        }

        $this->register_attempt($this->reset_key(), $this->minutes_to_seconds('reset_lockout_minutes', self::DEFAULT_RESET_LOCKOUT_MINUTES));
    }

    private function is_locked_out(string $key): bool
    {
        [$count, $limit] = $this->state_for_key($key);

        return $count >= $limit;
    }

    private function register_attempt(string $key, int $window): void
    {
        $count = (int) get_transient($key);

        set_transient($key, $count + 1, $window);
    }

    /** @return array{0: int, 1: int} */
    private function state_for_key(string $key): array
    {
        $is_reset_key = str_starts_with($key, 'upcore_reset_');
        $limit = $is_reset_key
            ? $this->int_field('max_reset_attempts', self::DEFAULT_MAX_RESET_ATTEMPTS)
            : $this->int_field('max_login_attempts', self::DEFAULT_MAX_LOGIN_ATTEMPTS);

        return [(int) get_transient($key), max(1, $limit)];
    }

    private function int_field(string $key, int $default): int
    {
        $value = $this->field($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    private function minutes_to_seconds(string $key, int $default): int
    {
        return max(1, $this->int_field($key, $default)) * MINUTE_IN_SECONDS;
    }

    private function login_key(): string
    {
        return 'upcore_login_' . md5($this->client_ip());
    }

    private function reset_key(): string
    {
        return 'upcore_reset_' . md5($this->client_ip());
    }

    private function client_ip(): string
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';

        return (string) apply_filters('upcore_login_throttle_client_ip', $ip);
    }
}
