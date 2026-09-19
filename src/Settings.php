<?php

declare(strict_types=1);

namespace UpCore;

final class Settings
{
    private const OPTION_KEY = 'upcore_modules';

    /** @return array<string, array{enabled?: bool, config?: array<string, string>}> */
    private function state(): array
    {
        $stored = get_option(self::OPTION_KEY, []);

        return is_array($stored) ? $stored : [];
    }

    public function is_enabled(string $slug): bool
    {
        $state = $this->state();

        return ! empty($state[$slug]['enabled']);
    }

    public function set_enabled(string $slug, bool $enabled): void
    {
        $state = $this->state();
        $state[$slug]['enabled'] = $enabled;

        update_option(self::OPTION_KEY, $state);
    }

    /** @return array<string, string> */
    public function get_config(string $slug): array
    {
        $state = $this->state();
        $config = $state[$slug]['config'] ?? [];

        return is_array($config) ? $config : [];
    }

    public function get_config_value(string $slug, string $key, string $default = ''): string
    {
        $config = $this->get_config($slug);

        return isset($config[$key]) && $config[$key] !== '' ? (string) $config[$key] : $default;
    }

    /** @param array<string, string> $config */
    public function set_config(string $slug, array $config): void
    {
        $state = $this->state();
        $existing = $state[$slug]['config'] ?? [];
        $state[$slug]['config'] = array_merge(is_array($existing) ? $existing : [], $config);

        update_option(self::OPTION_KEY, $state);
    }
}
