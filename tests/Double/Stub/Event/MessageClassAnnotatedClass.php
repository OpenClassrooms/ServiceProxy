<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Attribute\Event;

class CustomMessage
{
    public string $content;
}

class MessageClassAnnotatedClass
{
    #[Event(messageClass: CustomMessage::class)]
    public function handle(string $content): array
    {
        return compact('content');
    }
} 
