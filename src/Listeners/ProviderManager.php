<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Events\Interfaces\Listener;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Cache\Interfaces\Cache;
use Psr\EventDispatcher\ListenerProviderInterface;

#[Service]
class ProviderManager implements ListenerProviderInterface
{
    public function __construct(
        private readonly Cache|null     $cache,
        private readonly ListenerFinder $listenerFinder,
    )
    {
    }

    /** @return callable[] */
    public function getListenersForEvent(object $event): iterable
    {
        $listeners = $this->getListeners();

        foreach ($listeners as $listener) {
            if ($listener->eventName() === $event::class) {
                yield $listener->callable();
            }
        }
    }

    /** @return Listener[] $listeners */
    private function getListeners(): iterable
    {
        return $this->cache?->get(
            __CLASS__ . '::' . __METHOD__,
            fn() => $this->listenerFinder->findAll()
        ) ?? $this->listenerFinder->findAll();
    }
}
