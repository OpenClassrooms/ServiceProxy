<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotations;

/**
 * @Annotation
 */
class Cache implements ServiceProxyAnnotation
{
    private const MEMCACHE_KEY_MAX_LENGTH = 240;

    private const QUOTES_LENGTH = 4;

    public ?string $id = null;

    public ?string $namespace = null;

    public ?int $lifetime = null;

    /**
     * @throws InvalidCacheIdException
     */
    public function getId(): ?string
    {
        if (null !== $this->id && self::MEMCACHE_KEY_MAX_LENGTH + self::QUOTES_LENGTH < mb_strlen($this->id)) {
            throw new InvalidCacheIdException('id is too long, MUST be inferior to 240');
        }

        return $this->id;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getLifetime(): ?int
    {
        return $this->lifetime;
    }
}
