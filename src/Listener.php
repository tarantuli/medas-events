<?php

declare(strict_types=1);

namespace Medas\Events;

class Listener implements Interfaces\Listener
{
    private \Closure $callable;

    public function __construct(
        private readonly string $eventName,
        private readonly string $className,
        private readonly string $methodName,
    )
    {
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function callable(): callable
    {
        if (!isset($this->callable)) {
            $this->callable = (new \ReflectionMethod($this->className, $this->methodName))->getClosure(sm()->resolve($this->className));
        }

        return $this->callable;
    }
}
