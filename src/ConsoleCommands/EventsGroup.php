<?php

declare(strict_types=1);

namespace Medas\Events\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommandGroup, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;

#[Service]
readonly class EventsGroup extends BaseConsoleCommandGroup
{
    public function parent(): ConsoleCommandGroup|null
    {
        return null;
    }

    public function name(): string
    {
        return 'events';
    }
}
