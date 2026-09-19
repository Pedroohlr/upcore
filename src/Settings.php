<?php

declare(strict_types=1);

namespace UpCore;

final class Settings
{
    private const OPTION_KEY = 'upcore_modules';

    /** @return array<string, bool> */
    private function state(): array
    {
        $stored = get_option(self::OPTION_KEY, []);

        return is_array($stored) ? $stored : [];
    }

    public function is_enabled(string $slug): bool
    {
        $state = $this->state();

        return ! empty($state[$slug]);
    }

    public function set_enabled(string $slug, bool $enabled): void
    {
        $state = $this->state();
        $state[$slug] = $enabled;

        update_option(self::OPTION_KEY, $state);
    }
}
