<?php

declare(strict_types=1);

namespace UpCore;

abstract class Module
{
    public const STATUS_READY = 'ready';
    public const STATUS_PLANNED = 'planned';

    public function __construct(protected readonly Settings $settings)
    {
    }

    abstract public function slug(): string;

    abstract public function label(): string;

    abstract public function description(): string;

    abstract public function category(): string;

    abstract public function status(): string;

    abstract public function register(): void;

    /**
     * Campos de configuracao que este modulo expoe no painel (ex: chave de
     * API, numero maximo de tentativas). A maioria dos modulos nao precisa
     * de nenhum. Tipos suportados: text, password, number, checkbox.
     *
     * @return array<string, array{label: string, type: string, description?: string, default?: mixed}>
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Metricas que este modulo registra via UpCore\Stats. A maioria dos
     * modulos e "liga e esquece" e nao tem nada para contar.
     *
     * @return array<string, string> metric_key => label legivel
     */
    public function metrics(): array
    {
        return [];
    }

    /**
     * Config atual do modulo, ja resolvida com o valor padrao de cada campo
     * (declarado em fields()) quando o projeto nunca configurou nada.
     * checkbox vira bool, number vira float, o resto vira string.
     *
     * @return array<string, mixed>
     */
    public function resolved_config(): array
    {
        $stored = $this->settings->get_config($this->slug());
        $resolved = [];

        foreach ($this->fields() as $key => $field) {
            $type = $field['type'] ?? 'text';
            $default = $field['default'] ?? ($type === 'checkbox' ? false : '');

            if (! array_key_exists($key, $stored) || $stored[$key] === '') {
                $resolved[$key] = $default;

                continue;
            }

            $resolved[$key] = match ($type) {
                'checkbox' => in_array($stored[$key], ['1', 'true'], true),
                'number' => is_numeric($stored[$key]) ? (float) $stored[$key] : $default,
                default => (string) $stored[$key],
            };
        }

        return $resolved;
    }

    protected function field(string $key): mixed
    {
        return $this->resolved_config()[$key] ?? null;
    }

    protected function config(string $key, string $default = ''): string
    {
        return $this->settings->get_config_value($this->slug(), $key, $default);
    }
}
