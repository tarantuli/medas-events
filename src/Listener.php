<?php

declare(strict_types=1);

namespace Medas\Events;

class Listener implements Interfaces\Listener
{
    public function __construct(
        private readonly string   $eventName,
        private readonly \Closure $callable,
    )
    {
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function callable(): callable
    {
        return $this->callable;
    }
}
