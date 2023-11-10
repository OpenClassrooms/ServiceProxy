<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Cache extends Attribute
{
    /**
     * @param array<string>|string|null $handler
     * @param array<int, string> $pools
     * @param array<int, string> $tags
     */
    public function __construct(
        array|string|null     $handler = null,
        public readonly array $pools = [],
        public readonly ?int  $ttl = null,
        public readonly array $tags = [],
    ) {
        parent::__construct();
        $this->setHandlers($handler);
    }
}
