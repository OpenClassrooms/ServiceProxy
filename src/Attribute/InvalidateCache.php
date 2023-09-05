<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class InvalidateCache extends Attribute
{
    /**
     * @param array<int, string> $tags
     */
    public function __construct(
        ?string                $handler = null,
        ?string                $pool = null,
        private readonly array $tags = [],
    ) {
        parent::__construct();
        $this->setHandler(aliases: compact('handler', 'pool'));
    }

    /**
     * @return array<int, string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
