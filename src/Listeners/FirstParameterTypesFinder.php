<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\Service;
use Medas\Events\Exceptions\FirstParameterOfEventListenerIsNotAClass;

#[Service]
class FirstParameterTypesFinder
{
    /**
     * Returns the list of event class names that the given listener method handles.
     *
     * For a named type hint (`SomeEvent $event`) returns a single-element array.
     * For a union type hint (`EventA|EventB $event`) returns one entry per constituent
     * class type, allowing the listener to handle any of those event types.
     * Intersection types (`EventA&EventB`) are not supported because the dispatch key
     * cannot be unambiguously resolved to a single event class.
     *
     * @return string[]
     * @throws FirstParameterOfEventListenerIsNotAClass
     */
    public function find(\ReflectionMethod $method): array
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

        if ($type instanceof \ReflectionUnionType) {
            $types = [];

            foreach ($type->getTypes() as $subType) {
                if (!$subType instanceof \ReflectionNamedType || $subType->isBuiltin()) {
                    continue;
                }

                $types[] = $subType->getName();
            }

            if (!$types) {
                throw new FirstParameterOfEventListenerIsNotAClass($method);
            }

            return $types;
        }

        // ReflectionIntersectionType (TypeA&TypeB) cannot be dispatched by a single
        // event class name, so it is treated as an unsupported configuration.
        throw new FirstParameterOfEventListenerIsNotAClass($method);
    }
}
