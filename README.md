# medas-events

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

A PSR-14 compatible event dispatcher with automatic, attribute-based listener discovery and a lazy dispatch mode. Events are plain PHP objects — no base class or interface is required. Listeners are public methods on any `#[Service]` class annotated with `#[EventListener]`; the framework discovers them automatically at first use by scanning all registered service classes.

Key features:

- **Zero-configuration listener registration** — add `#[EventListener]` to a method, and it is found automatically; no manual wiring
- **Union type support** — a listener method typed `EventA|EventB $event` is registered for both event types
- **Lazy dispatch** — `lazyDispatch()` accepts the event class name and a factory callable; the factory is only invoked if at least one listener is registered, avoiding construction cost for unlistened events
- **Propagation stopping** — implement `Psr\EventDispatcher\StoppableEventInterface` and return `true` from `isPropagationStopped()` inside a listener to halt the chain
- **Result caching** — listener discovery runs once per request/process via the framework `cache()` helper

The `dispatch()` global function (provided by `medas-core`) delegates to the `EventDispatcher` service, so most code never needs to inject it directly.

## Usage

### Package developer context

Register the package:

```php
use Medas\Events\EventsPackage;

EventsPackage::instance();
```

**Defining an event:**

```php
readonly class UserRegistered
{
    public function __construct(
        public int    $userId,
        public string $email,
    ) {}
}
```

Any plain PHP object works. No base class or marker interface is needed.

**Registering a listener:**

```php
use Medas\Core\Attributes\{EventListener, Service};

#[Service]
readonly class WelcomeMailListener
{
    public function __construct(
        private Mailer $mailer,
    ) {}

    #[EventListener]
    public function onUserRegistered(UserRegistered $event): void
    {
        $this->mailer->sendWelcome($event->email);
    }
}
```

The method name is irrelevant — only the `#[EventListener]` attribute and the first parameter's type matter.

**Dispatching an event:**

```php
// Via the global helper (most common)
dispatch(new UserRegistered(42, 'alice@example.com'));

// Via the injected service (when you need the returned event object)
use Medas\Events\EventDispatcher;
use Medas\Core\Attributes\Service;

#[Service]
readonly class RegistrationService
{
    public function __construct(
        private EventDispatcher $dispatcher,
    ) {}

    public function register(string $email): void
    {
        // ... create user ...

        // dispatch() returns the event after all listeners have run,
        // allowing you to read any state listeners may have set
        $event = $this->dispatcher->dispatch(new UserRegistered($user->id, $email));
    }
}
```

**Union type listeners — handling multiple event types in one method:**

```php
#[EventListener]
public function onCreatedOrUpdated(InvoiceCreated|InvoiceUpdated $event): void
{
    // registered for both InvoiceCreated and InvoiceUpdated
    $this->index->reindex($event->invoice);
}
```

Intersection types (`TypeA&TypeB`) are not supported — `FirstParameterOfEventListenerIsNotAClass` is thrown at startup if one is encountered.

**Lazy dispatch — skip construction when no listeners are registered:**

```php
use Medas\Events\EventDispatcher;

// The closure is only called if at least one listener handles ReportGenerated
$event = $this->dispatcher->lazyDispatch(
    ReportGenerated::class,
    fn() => new ReportGenerated(
        report: $this->buildExpensiveReport(),
    ),
);

// $event is null when no listeners are registered
```

**Propagation stopping:**

```php
use Psr\EventDispatcher\StoppableEventInterface;

class LoginAttempt implements StoppableEventInterface
{
    public bool $blocked = false;

    public function isPropagationStopped(): bool
    {
        return $this->blocked;
    }
}
```

```php
#[EventListener]
public function checkBannedIp(LoginAttempt $event): void
{
    if ($this->ipBanList->contains($event->ip)) {
        $event->blocked = true;
        // No further listeners will run after this
    }
}
```

**Reading listener state after dispatch:**

```php
$event = $this->dispatcher->dispatch(new LoginAttempt($ip));

if ($event->blocked) {
    throw new AccessDeniedException();
}
```

### Backend user context

Event dispatching and listener registration are fully automatic — `dispatch()` is a global function available anywhere in the application, and listeners are discovered without any configuration beyond adding the `#[EventListener]` attribute.

If a listener is added to a service class but the event never fires, there is no cost beyond the one-time discovery scan (which is cached). Removing a listener is as simple as removing the `#[EventListener]` attribute or the method.

**Checking whether any listeners are registered for an event type** (e.g., to decide whether to bother preparing event data):

```php
// Use lazyDispatch instead of constructing the event manually
$this->dispatcher->lazyDispatch(
    HeavyProcessingCompleted::class,
    fn() => new HeavyProcessingCompleted($this->runHeavyProcessing()),
);
```
