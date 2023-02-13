<?php

declare(strict_types=1);

namespace Medas\Events\Exceptions;

use Medas\Core\Exceptions\BaseException;

class FirstParameterOfEventListenerIsNotAClass extends BaseException
{
    public function __construct(\ReflectionMethod $method)
    {
        parent::__construct($method->class . '::' . $method->name);
    }

    public function pattern(): string
    {
        return 'first parameter (if any) of event listener %s is not a class';
    }
}
