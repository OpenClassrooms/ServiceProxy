<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;

final class CacheHandlerMock implements CacheHandler
{
    public static ?int $lifeTime;

    private CacheProviderDecorator $cacheProvider;

    private bool $default;

    private string $name;

    public function __construct(?string $name = null, bool $default = true)
    {
        $this->name = $name ?? 'array';
        $this->cacheProvider = new CacheProviderDecorator(new ArrayCache());

        self::$lifeTime = null;
        $this->default = $default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function fetch(string $id, array $tags = [])
    {
        $namespaceId = $tags[0] ?? null;
        return $this->cacheProvider->fetchWithNamespace($id, $namespaceId);
    }

    public function save(string $id, $data, array $tags = [], $lifeTime = null): bool
    {
        self::$lifeTime = $lifeTime;
        $namespaceId = $tags[0] ?? null;
        return $this->cacheProvider->saveWithNamespace($id, $data, $namespaceId, $lifeTime);
    }

    public function contains(string $id, array $tags = []): bool
    {
        return $this->cacheProvider->contains($id);
    }

    public function isDefault(): bool
    {
        return $this->default;
    }
}
