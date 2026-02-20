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

        return $listeners;
    }

    /** @param array<string, EventListener[]> $listeners */
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
