<?php

declare(strict_types=1);

namespace Medas\EventsTest\Functional;

use Medas\Events\EventDispatcher;
use Medas\EventsTest\MockUps\{LazyDispatchWithListener, LazyDispatchWithoutListener};
use PHPUnit\Framework\TestCase;

class LazyDispatchTest extends TestCase
{
    public function testDispatchWithListener(): void
    {
        $result = service(EventDispatcher::class)->lazyDispatch(
            LazyDispatchWithListener::class,
            fn() => new LazyDispatchWithListener()
        );

        self::assertInstanceOf(LazyDispatchWithListener::class, $result);
        self::assertTrue($result->touchedByListener);
    }

    public function testDispatchWithoutListener(): void
    {
        $result = service(EventDispatcher::class)->lazyDispatch(
            LazyDispatchWithoutListener::class,
            fn() => new LazyDispatchWithoutListener()
        );

        self::assertNull($result);
    }
}
