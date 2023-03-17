<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;
use Symfony\Component\Cache\Adapter\AbstractTagAwareAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class SymfonyCacheHandler implements CacheHandler
{
    use ConfigurableHandler;

    private ?string $name;

    private AbstractTagAwareAdapter $cacheAdapter;

    public function __construct(AbstractTagAwareAdapter $cacheAdapter, ?string $name = null)
    {
        $this->cacheAdapter = $cacheAdapter;
        $this->name = $name;
    }

    public function fetch(string $id)
    {
        return $this->cacheAdapter->getItem($id)->get();
    }

    public function save(string $id, $data, ?int $lifeTime = null, array $tags = []): void
    {
        $this->cacheAdapter->get($id, function (ItemInterface $item) use ($tags, $data, $lifeTime) {
            $item->expiresAfter($lifeTime);
            $item->tag($tags);

            return $data;
        });
    }

    public function contains(string $id): bool
    {
        return $this->cacheAdapter->hasItem($id);
    }

    public function getName(): string
    {
        return $this->name ?? 'symfony_cache';
    }
}
