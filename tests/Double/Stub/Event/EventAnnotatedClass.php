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

    #[Event(name: 'first_event', methods: ['post'])]
    #[Event(name: 'first_event', methods: ['post'])]
    public function duplicatedEvent(mixed $useCaseRequest): mixed
    {
        return $useCaseRequest;
    }

    #[Event(name: 'event_name')]
    public function eventWithOnlyName(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event(name: 'first_event', methods: ['post'])]
    public function eventPost(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event(name: 'first_event', methods: ['pre'])]
    public function eventPre(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event(methods: ['onException'])]
    public function eventOnException(mixed $useCaseRequest): mixed
    {
        throw new \RuntimeException();
    }

    #[Event(name: 'wrong_event_name')]
    public function eventWithWrongName(mixed $useCaseRequest): int
    {
        return 1;
    }

    #[Event(name: 'first_event', methods: ['pre'])]
    #[Event(name: 'first_event', methods: ['post'])]
    #[Event(name: 'first_event', methods: ['post'])]
    #[Event(name: 'second_event', methods: ['post'])]
    #[Event(name: 'third_event', methods: ['pre', 'post', 'onException'])]
    #[Event(methods: ['pre', 'post', 'onException'])]
    public function multiEvents(mixed $useCaseRequest): int
    {
        return 1;
    }
}
