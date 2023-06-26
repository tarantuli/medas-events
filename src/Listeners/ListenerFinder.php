<?php

declare(strict_types=1);

namespace Medas\Events\Listeners;

use Medas\Events\Interfaces\EventListener;
use Medas\Events\Interfaces\Listener;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class ListenerFinder
{
    private array $listeners;

    public function __construct(
        private readonly FirstParameterTypesFinder $firstParameterTypesFinder,
    )
    {
    }

    /** @return Listener[] */
    public function findAll(): iterable
    {
        /** @var Listener[] $listeners */
        $this->listeners = [];

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
            $this->listeners[] = new \Medas\Events\Listener($type, $className, $method->name);
        }
    }
}
