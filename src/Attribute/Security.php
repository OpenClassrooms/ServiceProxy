<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Security extends Attribute
{
    /**
     * @param array<string>|string|null $handlers
     * @param array<string>|null $roles
     * @param class-string<\RuntimeException> $exception
     */
    public function __construct(
        public readonly ?string $expression = null,
        public array|string|null $handlers = null,
        public ?string $message = null,
        public ?string $exception = null,
        public ?array $roles = null,
    ) {
        parent::__construct();
        $this->setHandlers($handlers);
    }
}
