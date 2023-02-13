<?php

declare(strict_types=1);

namespace Medas\Events\Interfaces;

use Psr\EventDispatcher\EventDispatcherInterface;

interface EventDispatcher extends EventDispatcherInterface
{
    /**
     * The return value  is an object of type $event. This is specified in PhpStorm in .phpstorm.meta.php
     */
    public function dispatch(object $event): object;
}
