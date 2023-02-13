<?php

declare(strict_types=1);

namespace Medas\Events;

use Medas\Events\Listeners\ProviderManager;
use Medas\ServiceManager\Attributes\Service;
use Psr\EventDispatcher\StoppableEventInterface;

#[Service]
class EventDispatcher implements Interfaces\EventDispatcher
{
    public function __construct(
        private readonly ProviderManager $providerManager,
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
