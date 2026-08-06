<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\{EventListener as EventListenerAttribute, Service};
use Medas\Events\{EventListener, Exceptions\FirstParameterOfEventListenerIsNotAClass};

#[Service]
readonly class ListenerFinder
{
    public function __construct(
        private FirstParameterTypesFinder $firstParameterTypesFinder,
    )
    {
    }

    /** @return array<string, EventListener[]> */
    public function findAll(): array
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

                try {
                    $this->addListener($listeners, $method, $className);
                }
                catch (FirstParameterOfEventListenerIsNotAClass $e) {
                    throw new \LogicException(
                        sprintf(
                            'Event listener method %s::%s is marked with #[EventListener] but its first parameter is not a class type.',
                            $className,
                            $method->name,
                        ),
                        previous: $e,
                    );
                }
            }
        }

        // Higher priority runs first. usort is stable (PHP 8.0+), so listeners
        // with equal priority keep the order they were discovered in.
        return array_map(static function (array $typeListeners): array {
            usort(
                $typeListeners,
                static fn(EventListener $a, EventListener $b) => $b->priority() <=> $a->priority()
            );

            return $typeListeners;
        }, $listeners);
    }

    /** @param array<string, EventListener[]> $listeners */
    private function addListener(array &$listeners, \ReflectionMethod $method, string $className): void
    {
        $priority = attribute(EventListenerAttribute::class, $method)->priority;
        $types = $this->firstParameterTypesFinder->find($method);

        foreach ($types as $type) {
            if (!isset($listeners[$type])) {
                $listeners[$type] = [];
            }

            $listeners[$type][] = new EventListener($type, $className, $method->name, $priority);
        }
    }
}
