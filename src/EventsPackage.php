<?php

declare(strict_types=1);

namespace Medas\Events;

use Medas\Core\{AsSingleton, BasePackage};

class EventsPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}
