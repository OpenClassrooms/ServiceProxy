<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Annotation;

use OpenClassrooms\ServiceProxy\Annotation\Exception\InvalidCacheIdException;
use OpenClassrooms\ServiceProxy\Handler\Contract\AnnotationHandler;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Cache extends Annotation
{
    private const MEMCACHE_KEY_MAX_LENGTH = 240;

    private const QUOTES_LENGTH = 4;

    private ?string $id = null;

    private ?int $lifetime = null;

    private ?string $namespace = null;

    /**
     * @var array<int, string>
     */
    private array $tags = [];

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
     * @return array<int, string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @throws InvalidCacheIdException
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
        $maxLength = self::MEMCACHE_KEY_MAX_LENGTH + self::QUOTES_LENGTH;
        if ($this->id !== null && mb_strlen($this->id) > $maxLength) {
            throw new InvalidCacheIdException("id is too long, MUST be inferior to {$maxLength}");
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
     * @return class-string<AnnotationHandler>
     */
    public function getHandlerClass(): string
    {
        return CacheHandler::class;
    }

    /**
     * @param array<int, string> $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
