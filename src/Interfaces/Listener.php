<?php

declare(strict_types=1);

namespace Medas\Events\Interfaces;

interface Listener
{
    public function eventName(): string;

    public function callable(): callable;
}
