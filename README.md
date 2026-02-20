# medas-events

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

A PSR-14 compliant event dispatcher with automatic, attribute-based listener discovery and a **lazy dispatch** mode that avoids constructing the event object when no listeners are registered.

---

## Requirements

- PHP 8.4+
- [`morphp/medas-core`](https://github.com/tarantuli/medas-core) ^3

---

## Installation

```bash
composer require morphp/medas-events
```

Register the package with the service manager in your application bootstrap:

```php
use Medas\Events\EventsPackage;

EventsPackage::getInstance()->register();
```

---

## Concepts

### Events

An event is any plain PHP object. No base class or interface is required. If you want to support propagation stopping, implement PSR-14's `Psr\EventDispatcher\StoppableEventInterface`.

```php
class UserRegistered
{
    public function __construct(
        public readonly int $userId,
        public readonly string $email,
    ) {}
}
```

### Listeners

A listener is a public method on any class that is registered as a service (marked with `#[Service]`). Mark the method with `#[EventListener]`. The first (and only required) parameter determines which event type(s) the method handles.

```php
use Medas\Core\Attributes\{EventListener, Service};

#[Service]
readonly class WelcomeMailListener
{
    #[EventListener]
    public function onUserRegistered(UserRegistered $event): void
    {
        // send welcome email to $event->email
    }
}
```

**Union types** are supported — the method will be registered for each listed event type:

```php
#[EventListener]
public function onCreatedOrUpdated(UserCreated|UserUpdated $event): void
{
    // fires for both UserCreated and UserUpdated events
}
```

**Intersection types** (`TypeA&TypeB`) are not supported, because the dispatcher cannot unambiguously resolve a dispatch key from a compound type. An exception is thrown at startup if one is encountered.

### Propagation stopping

Implement `StoppableEventInterface` and return `true` from `isPropagationStopped()` inside a listener to prevent subsequent listeners from running.

```php
use Psr\EventDispatcher\StoppableEventInterface;

class UserLogin implements StoppableEventInterface
{
    public bool $blocked = false;

    public function isPropagationStopped(): bool
    {
        return $this->blocked;
    }
}
```

---

## Dispatching events

Resolve `EventDispatcher` from the service container and call `dispatch()`:

```php
use Medas\Events\EventDispatcher;

$dispatcher = service(EventDispatcher::class);

$event = $dispatcher->dispatch(new UserRegistered(42, 'user@example.com'));
```

The same instance is returned after all listeners have run, allowing you to read any state the listeners may have set.

### Lazy dispatch

`lazyDispatch()` accepts the event class name and a factory callable. The factory is only invoked if at least one listener is registered for the event type. This is useful when constructing the event is expensive.

```php
$event = $dispatcher->lazyDispatch(
    UserRegistered::class,
    fn() => new UserRegistered(
        userId: $this->repository->lastInsertId(),
        email:  $this->request->email(),
    ),
);

// $event is null when no listeners are registered, otherwise the dispatched object
```

---

## How listener discovery works

On first use, `ListenerFinder` iterates all classes registered with the service manager. For every method annotated with `#[EventListener]`, it inspects the first parameter's type hint to determine which event class(es) the listener handles. The results are cached via the framework's `cache()` helper so discovery only happens once per request/process.
