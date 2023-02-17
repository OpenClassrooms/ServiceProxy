<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotations;

use OpenClassrooms\ServiceProxy\Annotations\Exceptions\InvalidCacheIdException;
use OpenClassrooms\ServiceProxy\Contract\CacheHandler;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Cache extends Annotation
{
    private const MEMCACHE_KEY_MAX_LENGTH = 240;

    private const QUOTES_LENGTH = 4;

    private ?string $id = null;

    private ?int $lifetime = null;

    private ?string $namespace = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLifetime(): ?int
    {
        return $this->lifetime;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @throws \OpenClassrooms\ServiceProxy\Annotations\Exceptions\InvalidCacheIdException
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
        $maxLength = self::MEMCACHE_KEY_MAX_LENGTH + self::QUOTES_LENGTH;
        if (null !== $this->id && mb_strlen($this->id) > $maxLength) {
            throw new InvalidCacheIdException("id is too long, MUST be inferior to $maxLength");
        }
    }

    public function setLifetime(?int $lifetime): void
    {
        $this->lifetime = $lifetime;
    }

    public function setNamespace(?string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @return class-string<\OpenClassrooms\ServiceProxy\Contract\AnnotationHandler>
     */
    public function getHandlerClass(): string
    {
        return CacheHandler::class;
    }
}
