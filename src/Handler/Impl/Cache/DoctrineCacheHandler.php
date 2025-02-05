<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @deprecated use SymfonyCacheHandler instead
 */
final class DoctrineCacheHandler implements CacheHandler
{
    use ConfigurableHandler;

    private CacheItemPoolInterface $pool;

    public function __construct(CacheItemPoolInterface $pool = null, ?string $name = null)
    {
        $this->pool = $pool ?? new ArrayAdapter(storeSerialized: false);
        $this->name = $name;
    }

    public function fetch(string $poolName, string $id): CacheItemInterface
    {
        $tags = explode('|', $id);
        $id = array_shift($tags);

        return $this->fetchWithNamespace($id, $tags[0] ?? null);
    }

    public function save(string $poolName, string $id, $data, ?int $lifeTime = null, array $tags = []): void
    {
        $namespaceId = $tags[0] ?? null;

        if ($namespaceId !== null) {
            $namespace = $this->doFetch($namespaceId);
            if (!$namespace->isHit()) {
                $namespace->set($namespaceId . '_' . mt_rand(0, 1000000))
                    // 7 days as no expiration can prevent cache eviction forever (like redis)
                    ->expiresAfter(7 * 24 * 60 * 60);

                $this->pool->save($namespace);
            }
            $id = $namespace->get() . $id;
        }

        $item = $this->doFetch($id);

        $item->set($data)
            ->expiresAfter($lifeTime ?? 3600);

        $this->pool->save($item);
    }

    public function invalidateTags(string $poolName, array $tags): void
    {
        throw new \BadMethodCallException('Cache provider does not support tags invalidation');
    }

    public function getName(): string
    {
        return $this->name ?? 'doctrine_array';
    }

    private function fetchWithNamespace(string $id, string $namespaceId = null): CacheItemInterface
    {
        if ($namespaceId !== null) {
            $namespace = $this->doFetch($namespaceId);

            if ($namespace->isHit()) {
                $id = $namespace->get() . $id;
            }
        }

        return $this->doFetch($id);
    }

    private function doFetch(string $id): CacheItemInterface
    {
        return $this->pool->getItem(rawurlencode($id));
    }
}
