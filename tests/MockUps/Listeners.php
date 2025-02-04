<?php

declare(strict_types=1);

namespace Medas\EventsTest\MockUps;

use Medas\Core\Attributes\{EventListener, Service};

#[Service]
readonly class Listeners
{
    #[EventListener]
    public function forLazyDispatch(LazyDispatchWithListener $event): void
    {
        $event->touchedByListener = true;
    }
}
