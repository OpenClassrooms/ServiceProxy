<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class SymfonyCacheHandler implements CacheHandler
{
    use ConfigurableHandler;

    private ?int $defaultLifetime;

    private TagAwareAdapterInterface $cacheAdapter;

    public function __construct(
        ?TagAwareAdapterInterface $cacheAdapter = null,
        ?string $name = null,
        ?int $defaultLifetime = null
    ) {
        $this->cacheAdapter = $cacheAdapter ?? new TagAwareAdapter(new ArrayAdapter());
        $this->name = $name;
        $this->defaultLifetime = $defaultLifetime;
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
            ->expiresAfter($lifeTime ?? $this->defaultLifetime)
            ->tag($tags)
        ;

        $this->cacheAdapter->save($item);
    }

    public function contains(string $id): bool
    {
        return $this->cacheAdapter->hasItem($id);
    }

    public function invalidateTags(array $tags): void
    {
        $this->cacheAdapter->invalidateTags($tags);
    }

    public function getName(): string
    {
        return $this->name ?? 'array';
    }
}
