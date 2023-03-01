<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

final class EventStub
{
    private array $data;

    private string $name;

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
