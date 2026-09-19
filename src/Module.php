<?php

declare(strict_types=1);

namespace UpCore;

abstract class Module
{
    public const STATUS_READY = 'ready';
    public const STATUS_PLANNED = 'planned';

    abstract public function slug(): string;

    abstract public function label(): string;

    abstract public function description(): string;

    abstract public function category(): string;

    abstract public function status(): string;

    abstract public function register(): void;
}
