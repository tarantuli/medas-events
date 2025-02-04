<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\{EventListener as EventListenerAttribute, Service};
use Medas\Events\EventListener;

#[Service]
readonly class ListenerFinder
{
    public function __construct(
        private FirstParameterTypesFinder $firstParameterTypesFinder,
    )
    {
    }

    /** @return EventListener[][] */
    public function findAll(): iterable
    {
        $listeners = [];

        foreach (sm()->getServiceClassNames() as $className) {
            $class = new \ReflectionClass($className);

            if ($class->isAbstract()) {
                continue;
            }

            foreach ($class->getMethods() as $method) {
                if (!$method->getAttributes(EventListenerAttribute::class)) {
                    continue;
                }

                $this->addListener($listeners, $method, $className);
            }
        }

        return $listeners;
    }

    private function addListener(array &$listeners, \ReflectionMethod $method, string $className): void
    {
        $types = $this->firstParameterTypesFinder->find($method);

        foreach ($types as $type) {
            if (!isset($listeners[$type])) {
                $listeners[$type] = [];
            }

            $listeners[$type][] = new EventListener($type, $className, $method->name);
        }
    }
}
