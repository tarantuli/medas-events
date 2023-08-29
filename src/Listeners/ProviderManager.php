<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\CacheManager;
use Medas\Events\Interfaces\Listener;
use Psr\EventDispatcher\ListenerProviderInterface;

#[Service]
readonly class ProviderManager implements ListenerProviderInterface
{
    public function __construct(
        private CacheManager   $cacheManager,
        private ListenerFinder $listenerFinder,
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
        return $this->cacheManager->get()->get(
            __CLASS__ . '::' . __METHOD__,
            fn() => $this->listenerFinder->findAll()
        );
    }
}
