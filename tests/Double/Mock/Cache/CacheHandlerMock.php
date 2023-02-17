<?php

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Contract\CacheHandler;

class CacheHandlerMock implements CacheHandler
{
    public static ?int $lifeTime;

    private CacheProviderDecorator $cacheProvider;

    public function __construct()
    {
        $this->cacheProvider = new CacheProviderDecorator(new ArrayCache());
        
        self::$lifeTime = null;
    }

    public function getName(): string
    {
        return 'array';
    }

    public function fetchWithNamespace(string $id, ?string $namespaceId = null)
    {
        return $this->cacheProvider->fetchWithNamespace($id, $namespaceId);
    }

    public function saveWithNamespace(string $id, $data, ?string $namespaceId = null, $lifeTime = null): bool
    {
        self::$lifeTime = $lifeTime;

        return $this->cacheProvider->saveWithNamespace($id, $data, $namespaceId, $lifeTime);
    }

    public function contains(string $id): bool
    {
        return $this->cacheProvider->contains($id);
    }

    public function fetch(string $id)
    {
        return $this->cacheProvider->fetch($id);
    }

    public function save(string $id, $data, ?int $lifeTime = null): bool
    {
        return $this->cacheProvider->save($id, $data, $lifeTime);
    }
}
