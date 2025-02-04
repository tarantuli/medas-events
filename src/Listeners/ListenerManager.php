<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\{Attributes\Service, Interfaces\CacheManager};
use Psr\EventDispatcher\ListenerProviderInterface;

#[Service]
readonly class ListenerManager implements ListenerProviderInterface
{
    private array $listeners;

    public function __construct(
        private CacheManager   $cacheManager,
        private ListenerFinder $listenerFinder,
    )
    {
        $this->listeners = $this->cacheManager->get()->get(
            __CLASS__,
            fn() => $this->listenerFinder->findAll()
        );
    }

    /** @return callable[] */
    public function getListenersForEvent($event): iterable
    {
        foreach ($this->listeners[$event::class] ?? [] as $listener) {
            yield $listener->callable();
        }
    }

    /** @return callable[] */
    public function getListenersForEventType(string $eventType): iterable
    {
        foreach ($this->listeners[$eventType] ?? [] as $listener) {
            yield $listener->callable();
        }
    }
}
