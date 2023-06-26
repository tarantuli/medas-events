<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\Service;
use Medas\Events\Exceptions\FirstParameterOfEventListenerIsNotAClass;

#[Service]
class FirstParameterTypesFinder
{
    /** @return string[] */
    public function find(\ReflectionMethod $method): iterable
    {
        if ($method->getNumberOfParameters() < 1) {
            throw new FirstParameterOfEventListenerIsNotAClass($method);
        }

        $type = $method->getParameters()[0]->getType();

        if ($type === null) {
            throw new FirstParameterOfEventListenerIsNotAClass($method);
        }

        if ($type instanceof \ReflectionNamedType) {
            if ($type->isBuiltin()) {
                throw new FirstParameterOfEventListenerIsNotAClass($method);
            }

            return [$type->getName()];
        }

        elseif ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            $types = [];
            foreach ($type->getTypes() as $subType) {
                if (!$subType->isBuiltin()) {
                    $types[] = $subType->getName();
                }
            }

            if (!$types) {
                throw new FirstParameterOfEventListenerIsNotAClass($method);
            }

            return $types;
        }

        throw new FirstParameterOfEventListenerIsNotAClass($method);
    }
}
