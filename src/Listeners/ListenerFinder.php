<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Core\Attributes\Service;
use Medas\Core\Collections\GenericCollection;
use Medas\Events\Interfaces\EventListener;
use Medas\Events\Listener;

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

    /** @return Listener[] */
    public function findAll(): iterable
    {
        foreach (sm()->getServiceClassNames() as $className) {
            $class = new \ReflectionClass($className);

            if ($class->isAbstract()) {
                continue;
            }

            foreach ($class->getMethods() as $method) {
                if (!$method->getAttributes(EventListener::class)) {
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
            $this->listeners->offsetSet(null, new Listener($type, $className, $method->name));
        }
    }
}
