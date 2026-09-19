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
     * API). A maioria dos modulos nao precisa de nenhum.
     *
     * @return array<string, array{label: string, type: string, description?: string}>
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

    protected function config(string $key, string $default = ''): string
    {
        return $this->settings->get_config_value($this->slug(), $key, $default);
    }
}
