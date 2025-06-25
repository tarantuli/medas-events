<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\Service;
use Medas\Events\EventListener;
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
    public function getListenersForEvent($event): iterable
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

    /** @return EventListener[][] */
    private function getListeners(): iterable
    {
        return cache(__CLASS__, fn() => $this->listenerFinder->findAll());
    }
}
