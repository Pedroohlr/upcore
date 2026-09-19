<?php

declare(strict_types=1);

namespace UpCore;

final class ModuleManager
{
    /** @var array<string, Module> */
    private array $modules = [];

    public function add(Module $module): void
    {
        $this->modules[$module->slug()] = $module;
    }

    /** @return array<string, Module> */
    public function all(): array
    {
        return $this->modules;
    }

    public function get(string $slug): ?Module
    {
        return $this->modules[$slug] ?? null;
    }

    public function boot(Settings $settings): void
    {
        foreach ($this->modules as $module) {
            if ($settings->is_enabled($module->slug())) {
                $module->register();
            }
        }
    }
}
