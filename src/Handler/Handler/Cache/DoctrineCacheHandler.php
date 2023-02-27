<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Cache;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;

final class DoctrineCacheHandler implements CacheHandler
{
    use ConfigurableHandler;

    private CacheProviderDecorator $cacheProvider;

    private ?string $name;

    public function __construct(?CacheProviderDecorator $cacheProvider = null, ?string $name = null)
    {
        $this->cacheProvider = $cacheProvider ?? new CacheProviderDecorator(new ArrayCache());
        $this->name = $name;
    }

    public function fetch(string $id, array $tags = [])
    {
        return $this->cacheProvider->fetchWithNamespace($id, $tags[0] ?? null);
    }

    public function save(string $id, $data, array $tags = [], ?int $lifeTime = null): bool
    {
        return $this->cacheProvider->saveWithNamespace($id, $data, $tags[0] ?? null, $lifeTime);
    }

    public function contains(string $id, array $tags = []): bool
    {
        return $this->cacheProvider->contains($id);
    }

    public function getName(): string
    {
        return $this->name ?? 'request_scope';
    }
}
