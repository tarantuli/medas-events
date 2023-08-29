<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\{EventListener as EventListenerAttribute, Service};
use Medas\Core\Collections\GenericCollection;
use Medas\Events\EventListener;

#[Service]
readonly class ListenerFinder
{
    private GenericCollection $listeners;

    public function __construct(
        private FirstParameterTypesFinder $firstParameterTypesFinder,
    )
    {
        $this->listeners = new GenericCollection();
    }

    /** @return EventListener[] */
    public function findAll(): iterable
    {
        foreach (sm()->getServiceClassNames() as $className) {
            $class = new \ReflectionClass($className);

            if ($class->isAbstract()) {
                continue;
            }

            foreach ($class->getMethods() as $method) {
                if (!$method->getAttributes(EventListenerAttribute::class)) {
                    continue;
                }

                $this->addListener($method, $className);
            }
        }

        return $this->listeners;
    }

    private function addListener(\ReflectionMethod $method, string $className): void
    {
        $types = $this->firstParameterTypesFinder->find($method);

        foreach ($types as $type) {
            $this->listeners->offsetSet(null, new EventListener($type, $className, $method->name));
        }
    }
}
