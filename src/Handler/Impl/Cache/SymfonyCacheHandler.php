<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

final class SymfonyCacheHandler implements CacheHandler
{
    use ConfigurableHandler;

    /**
     * @var iterable<string, TagAwareAdapter>
     */
    private iterable $pools;

    /**
     * @param iterable<string, TagAwareAdapter> $pools
     */
    public function __construct(
        iterable $pools = [],
        ?string $name = null
    ) {
        $this->name = $name;
        $this->pools = $pools;
    }

    public function fetch(string $poolName, string $id): mixed
    {
        $pool = $this->getPools([$poolName])[$poolName];

        return $pool->getItem($id)->get();
    }

    public function save(array $pools, string $id, $data, ?int $ttl = null, array $tags = []): void
    {
        foreach ($this->getPools($pools) as $pool) {
            $this->doSave($pool, $id, $data, $ttl, $tags);
        }
    }

    public function contains(string $poolName, string $id): bool
    {
        foreach ($this->getPools([$poolName]) as $pool) {
            if ($pool->hasItem($id)) {
                return true;
            }
        }

        return false;
    }

    public function invalidateTags(array $pools, array $tags): void
    {
        foreach ($this->getPools($pools) as $pool) {
            $pool->invalidateTags($tags);
        }
    }

    public function getName(): string
    {
        return $this->name ?? 'array';
    }

    /**
     * @param mixed $data
     * @param array<int, string> $tags
     */
    private function doSave(TagAwareAdapter $pool, string $id, $data, ?int $ttl = null, array $tags = []): void
    {
        $item = $pool->getItem($id)
            ->set($data)
            ->expiresAfter($ttl)
            ->tag($tags)
        ;

        $pool->save($item);
    }

    /**
     * @param array<int, string> $poolNames
     *
     * @return array<string, TagAwareAdapter>
     */
    private function getPools(array $poolNames): array
    {
        $existingPools = [...$this->pools];

        $pools = array_filter(
            $existingPools,
            static fn (string $key) => \in_array($key, $poolNames, true),
            \ARRAY_FILTER_USE_KEY
        );

        if (\count($pools) === 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No cache pools found for "%s". Available pools are: "%s".',
                    \count($poolNames) > 1 ? implode('", "', $poolNames) : $poolNames[0],
                    implode('", "', array_keys($existingPools))
                )
            );
        }

        return $pools;
    }
}
