<?php

declare(strict_types=1);

namespace Medas\Events;

use Medas\Core\Attributes\Service;
use Medas\Events\Listeners\ListenerManager;
use Psr\EventDispatcher\StoppableEventInterface;

#[Service]
readonly class EventDispatcher implements Interfaces\EventDispatcher
{
    public function __construct(
        private ListenerManager $providerManager,
    )
    {
    }

    public function dispatch(object $event): object
    {
        foreach ($this->providerManager->getListenersForEvent($event) as $listener) {
            $listener($event);

            if ($listener instanceof StoppableEventInterface) {
                if ($listener->isPropagationStopped()) {
                    return $event;
                }
            }
        }

        return $event;
    }
}
