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

    public function aliases(): array
    {
        return ['event-listeners', 'listeners'];
    }

    public function description(): string
    {
        return 'List all registered event listeners';
    }

    public function process(CommandInput $input): void
    {
        $allListeners = $this->listenerManager->getListeners();

        ksort($allListeners);

        foreach ($allListeners as $eventType => $listeners) {
            $this->printer->printLine(Text::create($eventType, SafeColor::Yellow));

            $descriptions = [];
            $priorities = [];

            foreach ($listeners as $listener) {
                $descriptions[] = $listener instanceof EventListener
                    ? $listener->classAndMethod()
                    : $this->callableDescriber->describe($listener);

                $priorities[] = $listener instanceof EventListener ? $listener->priority() : 0;
            }

            foreach ($descriptions as $index => $description) {
                $blocks = [Text::create('   ' . $description, SafeColor::Green)];

                if ($priorities[$index] !== 0) {
                    $blocks[] = Text::create(' at priority ');
                    $blocks[] = Text::create((string) $priorities[$index], SafeColor::Cyan);
                }

                $this->printer->printLine(...$blocks);
            }

            $this->printer->printEol();
        }
    }
}
