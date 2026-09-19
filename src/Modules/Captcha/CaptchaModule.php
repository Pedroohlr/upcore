<?php

declare(strict_types=1);

namespace UpCore\Modules\Captcha;

use UpCore\Module;
use UpCore\Stats;
use WP_Error;

final class CaptchaModule extends Module
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const DEFAULT_THRESHOLD = 0.5;

    public function slug(): string
    {
        return 'captcha';
    }

    public function label(): string
    {
        return __('CAPTCHA', 'upcore');
    }

    public function description(): string
    {
        return __('Adiciona reCAPTCHA v3 (invisivel) no login, recuperacao de senha e cadastro. Preencha a site key e a secret key abaixo (ou defina UPCORE_RECAPTCHA_SITE_KEY/UPCORE_RECAPTCHA_SECRET_KEY no wp-config.php); sem isso o modulo fica inerte.', 'upcore');
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
            'site_key' => [
                'label' => __('Site Key', 'upcore'),
                'type' => 'text',
                'description' => __('Site key do reCAPTCHA v3 (google.com/recaptcha/admin).', 'upcore'),
            ],
            'secret_key' => [
                'label' => __('Secret Key', 'upcore'),
                'type' => 'password',
                'description' => __('Secret key do reCAPTCHA v3.', 'upcore'),
            ],
        ];
    }

    public function metrics(): array
    {
        return [
            'failed' => __('Verificacoes de CAPTCHA reprovadas', 'upcore'),
        ];
    }

    public function register(): void
    {
        if (! $this->is_configured()) {
            return;
        }

        add_action('login_enqueue_scripts', [$this, 'enqueue_recaptcha']);
        add_action('login_form', [$this, 'render_hidden_field']);
        add_action('lostpassword_form', [$this, 'render_hidden_field']);
        add_action('register_form', [$this, 'render_hidden_field']);

        // Prioridade 25: depois de wp_authenticate_username_password (20), para
        // nao ser ignorado por ele (callbacks do core nao respeitam um WP_Error
        // recebido de um filtro anterior e seguem checando a senha).
        add_filter('authenticate', [$this, 'block_if_captcha_invalid'], 25);
        add_action('lostpassword_post', [$this, 'guard_password_reset'], 10, 2);
        add_filter('registration_errors', [$this, 'guard_registration'], 10, 3);
    }

    public function enqueue_recaptcha(): void
    {
        wp_enqueue_script(
            'upcore-recaptcha',
            'https://www.google.com/recaptcha/api.js?render=' . rawurlencode($this->site_key()),
            [],
            null,
            true
        );

        wp_add_inline_script('upcore-recaptcha', $this->inline_script(), 'after');
    }

    public function render_hidden_field(): void
    {
        echo '<input type="hidden" name="upcore_recaptcha_token" value="" />';
    }

    /**
     * @param mixed $user
     * @return mixed|WP_Error
     */
    public function block_if_captcha_invalid($user)
    {
        if (($GLOBALS['pagenow'] ?? '') !== 'wp-login.php') {
            return $user;
        }

        if ($this->verify_token($this->submitted_token())) {
            return $user;
        }

        Stats::record($this->slug(), 'failed');

        return new WP_Error(
            'upcore_recaptcha_failed',
            __('Nao foi possivel validar o CAPTCHA. Tente novamente.', 'upcore')
        );
    }

    /**
     * @param mixed $user_data
     */
    public function guard_password_reset(WP_Error $errors, $user_data = null): void
    {
        if (! $this->verify_token($this->submitted_token())) {
            Stats::record($this->slug(), 'failed');

            $errors->add(
                'upcore_recaptcha_failed',
                __('Nao foi possivel validar o CAPTCHA. Tente novamente.', 'upcore')
            );
        }
    }

    public function guard_registration(WP_Error $errors, string $sanitized_user_login, string $user_email): WP_Error
    {
        if (! $this->verify_token($this->submitted_token())) {
            Stats::record($this->slug(), 'failed');

            $errors->add(
                'upcore_recaptcha_failed',
                __('Nao foi possivel validar o CAPTCHA. Tente novamente.', 'upcore')
            );
        }

        return $errors;
    }

    private function submitted_token(): string
    {
        return isset($_POST['upcore_recaptcha_token']) ? (string) $_POST['upcore_recaptcha_token'] : '';
    }

    private function verify_token(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        $response = wp_remote_post(self::VERIFY_URL, [
            'timeout' => 5,
            'body' => [
                'secret' => $this->secret_key(),
                'response' => $token,
                'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '',
            ],
        ]);

        // Falha de rede ate o Google nao deve travar o login de ninguem.
        if (is_wp_error($response)) {
            return true;
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if (! is_array($body) || empty($body['success'])) {
            return false;
        }

        $score = isset($body['score']) ? (float) $body['score'] : 1.0;

        return $score >= $this->threshold();
    }

    private function inline_script(): string
    {
        $site_key = esc_js($this->site_key());

        return <<<JS
(function() {
	function upcoreFillRecaptchaToken() {
		var fields = document.querySelectorAll('input[name="upcore_recaptcha_token"]');
		if (! fields.length || typeof grecaptcha === 'undefined') {
			return;
		}
		grecaptcha.ready(function() {
			grecaptcha.execute('{$site_key}', { action: 'upcore_login' }).then(function(token) {
				fields.forEach(function(field) {
					field.value = token;
				});
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', upcoreFillRecaptchaToken);
	} else {
		upcoreFillRecaptchaToken();
	}
})();
JS;
    }

    private function is_configured(): bool
    {
        return $this->site_key() !== '' && $this->secret_key() !== '';
    }

    private function site_key(): string
    {
        if (defined('UPCORE_RECAPTCHA_SITE_KEY') && UPCORE_RECAPTCHA_SITE_KEY !== '') {
            return (string) UPCORE_RECAPTCHA_SITE_KEY;
        }

        return $this->config('site_key');
    }

    private function secret_key(): string
    {
        if (defined('UPCORE_RECAPTCHA_SECRET_KEY') && UPCORE_RECAPTCHA_SECRET_KEY !== '') {
            return (string) UPCORE_RECAPTCHA_SECRET_KEY;
        }

        return $this->config('secret_key');
    }

    private function threshold(): float
    {
        return defined('UPCORE_RECAPTCHA_THRESHOLD') ? (float) UPCORE_RECAPTCHA_THRESHOLD : self::DEFAULT_THRESHOLD;
    }
}
