<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class InvalidateCache extends Attribute
{
    /**
     * @param array<string>|string|null $handler
     * @param array<int, string> $tags
     * @param array<int, string> $pools
     */
    public function __construct(
        protected array|string|null      $handler = null,
        public readonly array $pools = [],
        public readonly array $tags = [],
    ) {
        parent::__construct();
    }
}
