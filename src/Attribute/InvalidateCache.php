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
        private readonly array $pools = [],
        private readonly array $tags = [],
    ) {
        parent::__construct();
    }

    /**
     * @return array<int, string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return array<int, string>
     */
    public function getPools(): array
    {
        return $this->pools;
    }
}
