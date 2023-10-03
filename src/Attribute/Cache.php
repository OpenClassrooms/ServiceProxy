<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Cache extends Attribute
{
    /**
     * @param array<int, string> $tags
     * @param array<int, string> $pools
     */
    public function __construct(
        protected ?string     $handler = null,
        public readonly array $pools = [],
        public readonly ?int  $ttl = null,
        public readonly array $tags = [],
    ) {
        parent::__construct();
    }
}
