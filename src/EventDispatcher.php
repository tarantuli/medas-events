<?php

declare(strict_types=1);

namespace Medas\Events;

use Medas\Core\{Attributes\Service, Interfaces\EventDispatcher as EventDispatcherInterface};
use Psr\EventDispatcher\StoppableEventInterface;

#[Service]
readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private Listeners\ListenerManager $listenerManager,
    )
    {
    }

    public function dispatch(object $event): object
    {
        foreach ($this->listenerManager->getListenersForEvent($event) as $listener) {
            $listener($event);

            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }
        }

        return $event;
    }

    public function lazyDispatch(string $eventType, callable $callable): object|null
    {
        $event = null;

        foreach ($this->listenerManager->getListenersForEventType($eventType) as $listener) {
            if ($event === null) {
                $event = $callable();
            }

            $listener($event);

            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }
        }

        return $event;
    }
}
