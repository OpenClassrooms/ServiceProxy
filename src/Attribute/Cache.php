<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Cache extends Attribute
{
    /**
     * @param array<int, string> $tags
     */
    public function __construct(
        ?string $handler = null,
        ?string $pool = null,
        private readonly ?int $lifetime = null,
        private readonly array $tags = []
    ) {
        if ($pool !== null && $handler !== null && $handler !== $pool) {
            throw new \RuntimeException(
                'Argument \'pool\' is an alias for \'handler\'.
                You can only define one of the two arguments.'
            );
        }

        if ($pool !== null) {
            $handler = $pool;
        }

        parent::__construct($handler);
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
