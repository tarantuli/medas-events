<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\EventListener;
use Psr\EventDispatcher\ListenerProviderInterface;

#[Service]
readonly class ListenerManager implements ListenerProviderInterface
{
    public function __construct(
        private ListenerFinder $listenerFinder,
    )
    {
    }

    /** @return callable[] */
    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->getListeners()[$event::class] ?? [] as $listener) {
            yield $listener->callable();
        }
    }

    /** @return callable[] */
    public function getListenersForEventType(string $eventType): iterable
    {
        foreach ($this->getListeners()[$eventType] ?? [] as $listener) {
            yield $listener->callable();
        }
    }

    /** @return array<string, EventListener[]> */
    private function getListeners(): array
    {
        return cache(__CLASS__, fn() => $this->listenerFinder->findAll());
    }
}
