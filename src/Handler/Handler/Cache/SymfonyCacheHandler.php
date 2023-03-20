<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class SymfonyCacheHandler implements CacheHandler
{
    use ConfigurableHandler;

    private ?string $name;

    private TagAwareAdapterInterface $cacheAdapter;

    public function __construct(TagAwareAdapterInterface $cacheAdapter, ?string $name = null)
    {
        $this->cacheAdapter = $cacheAdapter;
        $this->name = $name;
    }

    public function fetch(string $id)
    {
        return $this->cacheAdapter
            ->getItem($id)
            ->get()
        ;
    }

    public function save(string $id, $data, ?int $lifeTime = null, array $tags = []): void
    {
        $item = $this->cacheAdapter->getItem($id)
            ->set($data)
            ->expiresAfter($lifeTime)
            ->tag($tags)
        ;

        $this->cacheAdapter->save($item);
    }

    public function contains(string $id): bool
    {
        return $this->cacheAdapter->hasItem($id);
    }

    public function invalidateTags(array $tags): bool
    {
        return $this->cacheAdapter->invalidateTags($tags);
    }

    public function getName(): string
    {
        return $this->name ?? 'array';
    }
}
