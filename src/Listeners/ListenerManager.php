<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\{Attributes\Service, Interfaces\EventListener};
use Psr\EventDispatcher\ListenerProviderInterface;

#[Service]
class ListenerManager implements ListenerProviderInterface
{
    /** @var array<string, EventListener[]>|null */
    private array|null $listeners = null;

    public function __construct(
        private readonly ListenerFinder $listenerFinder,
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
    public function getListeners(): array
    {
        // The table is derived from the code, so it cannot change while the process
        // runs. The cache stays the source, so a persistent cache still spares a fresh
        // process the scan; holding the result here only avoids asking the cache on
        // every dispatch, which happens tens of thousands of times per request.
        return $this->listeners ??= cache(__CLASS__, fn() => $this->listenerFinder->findAll());
    }
}
