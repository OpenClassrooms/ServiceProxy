<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Cache extends Attribute
{
    /**
     * @param array<string>|string|null $handler
     * @param array<string>|string|null $pool
     * @param array<int, string> $tags
     */
    public function __construct(
        array|string|null      $handler = null,
        array|string|null      $pool = null,
        private readonly ?int  $lifetime = null,
        private readonly array $tags = [],
    ) {
        parent::__construct();
        $this->setHandlers(aliases: compact('handler', 'pool'));
    }

    public function getLifetime(): ?int
    {
        return $this->lifetime;
    }

    /**
     * @return array<int, string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
