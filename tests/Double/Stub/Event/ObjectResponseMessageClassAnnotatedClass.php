<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;

class CustomMessage
{
    public function __construct(
        public string $content,
        public int $id,
        public \DateTimeImmutable $createdAt,
        public Metadata $meta = new Metadata(active: false),
    ) {
    }
}

class Metadata
{
    public function __construct(
        public readonly bool $active,
        public readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
    }
}

class ResponseObject
{
    public function __construct(
        public string $content,
        public int $id,
        public Metadata $meta,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}

class ObjectResponseMessageClassAnnotatedClass
{
    /**
     * @throws \Random\RandomException
     */
    #[Event(messageClass: CustomMessage::class)]
    public function handle(string $content): ResponseObject
    {
        return new ResponseObject(
            content: $content,
            id: random_int(1, 100),
            meta: new Metadata(active: true),
            createdAt: new \DateTimeImmutable(),
        );
    }
}
