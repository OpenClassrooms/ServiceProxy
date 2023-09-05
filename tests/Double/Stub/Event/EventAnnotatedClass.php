<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;

class EventAnnotatedClass
{
    #[Event]
    public function __invoke(mixed $useCaseRequest): int
    {
        return 1;
    }

    public function nonAnnotatedMethod(): bool
    {
        return true;
    }

    #[Event]
    public function annotatedMethodWithException(): void
    {
        throw new \RuntimeException();
    }

    #[Event]
    public function annotatedMethod(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event]
    public function annotatedMethodWithoutParams(): int
    {
        return 1;
    }

    #[Event]
    public function annotatedMethodWithoutReturn(): void
    {
    }

    #[Event(name: 'first_event', dispatch: [Event\On::POST])]
    #[Event(name: 'first_event', dispatch: [Event\On::POST])]
    public function duplicatedEvent(mixed $useCaseRequest): mixed
    {
        return $useCaseRequest;
    }

    #[Event(name: 'event_name')]
    public function eventWithOnlyName(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event(name: 'first_event', dispatch: [Event\On::POST])]
    public function eventPost(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event(name: 'first_event', dispatch: [Event\On::PRE])]
    public function eventPre(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event(dispatch: ['onException'])]
    public function eventOnException(mixed $useCaseRequest): mixed
    {
        throw new \RuntimeException();
    }

    #[Event(name: 'wrong_event_name')]
    public function eventWithWrongName(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event(name: 'first_event', dispatch: [Event\On::PRE])]
    #[Event(name: 'first_event', dispatch: [Event\On::POST])]
    #[Event(name: 'first_event', dispatch: [Event\On::POST])]
    #[Event(name: 'second_event', dispatch: [Event\On::POST])]
    #[Event(name: 'third_event', dispatch: [Event\On::PRE, Event\On::POST, Event\On::EXCEPTION])]
    #[Event(dispatch: [Event\On::PRE, Event\On::POST, Event\On::EXCEPTION])]
    public function multiEvents(mixed $useCaseRequest): int
    {
        return 1;
    }
}
