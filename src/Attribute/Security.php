<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Security extends Attribute
{
    /**
     * @param class-string<\RuntimeException> $exception
     */
    public function __construct(
        public ?string $handler = null,
        public readonly ?string $expression = null,
        public ?string $message = null,
        public ?string $exception = null,
    ) {
        parent::__construct();
        $this->setHandler($handler);
    }
}