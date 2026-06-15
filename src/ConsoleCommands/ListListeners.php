<?php

declare(strict_types=1);

namespace Medas\Events\ConsoleCommands;

use Medas\Console\{
    Commands\BaseConsoleCommand,
    Commands\CommandInput,
    Commands\ConsoleCommandGroup,
    Formats\SafeColor,
    Printer,
    Text
};
use Medas\Core\{Attributes\Service, CallableDescriber};
use Medas\Events\{EventListener, Listeners\ListenerManager};

#[Service]
readonly class ListListeners extends BaseConsoleCommand
{
    public function __construct(
        private CallableDescriber $callableDescriber,
        private EventsGroup       $group,
        private ListenerManager   $listenerManager,
        private Printer           $printer,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'list-listeners';
    }

    public function description(): string
    {
        return 'List all registered event listeners';
    }

    public function process(CommandInput $input): void
    {
        $allListeners = $this->listenerManager->getListeners();

        foreach ($allListeners as $eventType => $listeners) {
            $this->printer->printLine(Text::create($eventType, SafeColor::Yellow));

            foreach ($listeners as $listener) {
                $description = $listener instanceof EventListener
                    ? $listener->classAndMethod()
                    : $this->callableDescriber->describe($listener);

                $this->printer->printLine(Text::create('   ' . $description, SafeColor::Green));
            }

            $this->printer->printEol();
        }
    }
}
