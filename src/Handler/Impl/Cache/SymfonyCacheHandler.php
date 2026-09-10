<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class SymfonyCacheHandler implements CacheHandler
{
    use ConfigurableHandler;

    /**
     * @var iterable<string, TagAwareAdapterInterface>
     */
    private iterable $pools;

    /**
     * @param iterable<string, TagAwareAdapterInterface> $pools
     */
    public function __construct(iterable $pools = [])
    {
        $this->pools = $pools;
    }

    public function fetch(string $poolName, string $id): CacheItemInterface
    {
        $pool = $this->getPool($poolName);

        return $pool->getItem($id);
    }

    public function save(string $poolName, string $id, $data, ?int $ttl = null, array $tags = []): void
    {
        $pool = $this->getPool($poolName);

        $item = $pool->getItem($id)
            ->set($data)
            ->expiresAfter($ttl)
            ->tag($tags)
        ;

        $pool->save($item);
    }

    public function invalidateTags(string $poolName, array $tags): void
    {
        $this->getPool($poolName)
            ->invalidateTags($tags);
    }

    public function getName(): string
    {
        return 'symfony_cache';
    }

    private function getPool(string $poolName): TagAwareAdapterInterface
    {
        $existingPools = [...$this->pools];

        if (!isset($existingPools[$poolName])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No cache pool found for "%s". Available pools are: "%s".',
                    $poolName,
                    implode('", "', array_keys($existingPools))
                )
            );
        }

        return $existingPools[$poolName];
    }
}
