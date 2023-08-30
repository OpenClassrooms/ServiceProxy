<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Event extends Attribute
{
    public function __construct(
        public readonly string $topic,
    ) {
        parent::__construct();
    }

    public function getHandlerClass(): string
    {

    }
}
