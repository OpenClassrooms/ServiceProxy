<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Cache;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;

/**
 * @deprecated use SymfonyCacheHandler instead
 */
final class DoctrineCacheHandler implements CacheHandler
{
    use ConfigurableHandler;

    private CacheProviderDecorator $cacheProvider;

    public function __construct(?CacheProviderDecorator $cacheProvider = null, ?string $name = null)
    {
        $this->cacheProvider = $cacheProvider ?? new CacheProviderDecorator(new ArrayCache());
        $this->name = $name;
    }

    public function fetch(string $id)
    {
        $tags = explode('|', $id);
        $id = array_shift($tags);

        return $this->cacheProvider->fetchWithNamespace($id, $tags[0] ?? null);
    }

    public function save(string $id, $data, ?int $lifeTime = null, array $tags = []): void
    {
        $this->cacheProvider->saveWithNamespace($id, $data, $tags[0] ?? null, $lifeTime);
    }

    public function contains(string $id): bool
    {
        return $this->cacheProvider->contains($id);
    }

    public function invalidateTags(array $tags): void
    {
        throw new \BadMethodCallException('Cache provider does not support tags invalidation');
    }

    public function getName(): string
    {
        return $this->name ?? 'doctrine_array';
    }
}
