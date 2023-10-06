<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;

class ClassWithEventAttributes
{
    public function nonAnnotatedMethod(): bool
    {
        return true;
    }

    #[Event]
    public function annotatedMethodWithException(): array
    {
        throw new \RuntimeException();
    }

    #[Event]
    public function annotatedMethod($useCaseRequest): int
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

    #[Event(methods: 'post', name: 'first_event')]
    #[Event(methods: 'post', name: 'first_event')]
    public function duplicatedEvent($useCaseRequest): int
    {
        return 1;
    }

    #[Event(name: 'event_name')]
    public function eventWithOnlyName($useCaseRequest): int
    {
        return 1;
    }

    #[Event(methods: 'post')]
    public function eventPost($useCaseRequest): int
    {
        return 1;
    }

    #[Event(methods: 'pre')]
    public function eventPre($useCaseRequest): int
    {
        return 1;
    }

    #[Event(methods: 'onException')]
    public function eventOnException($useCaseRequest)
    {
        throw new \RuntimeException();

        /** @noinspection PhpUnreachableStatementInspection */
        return $useCaseRequest;
    }

    #[Event(methods: 'wrong_event_name')]
    public function eventWithWrongName($useCaseRequest): int
    {
        return 1;
    }

    #[Event(methods: 'pre,post,onException')]
    public function eventWithSeveralMethods($useCaseRequest): int
    {
        return 1;
    }

    #[Event(methods: 'pre', name: 'first_event')]
    #[Event(methods: 'post', name: 'first_event')]
    #[Event(methods: 'post', name: 'first_event')]
    #[Event(methods: 'post', name: 'second_event')]
    #[Event(methods: 'pre,post,onException', name: 'third_event')]
    #[Event(methods: 'pre,post,onException')]
    public function multiEvents($useCaseRequest): int
    {
        return 1;
    }

    #[Event(defaultPrefix: 'toto')]
    public function prefixedEvent($useCaseRequest): int
    {
        return 1;
    }

    #[Event(defaultPrefix: 'toto', name: 'first_event')]
    public function namedEventWithPrefix($useCaseRequest): int
    {
        return 1;
    }

    #[Event(defaultPrefix: '')]
    public function eventEmptyPrefix($useCaseRequest): int
    {
        return 1;
    }

    #[Event(useClassNameOnly: false)]
    public function EventWithMethodName($useCaseRequest): int
    {
        return 1;
    }
}
