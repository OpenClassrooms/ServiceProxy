<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\Cache\DoctrineCacheHandler;

final class DoctrineCacheHandlerMock implements CacheHandler
{
    public static ?int $lifeTime = null;

    private CacheHandler $wrappedHandler;

    private CacheProviderDecorator $cacheProvider;

    private bool $default;

    private string $name;

    public function __construct(?string $name = null, bool $default = true)
    {
        $this->name = $name ?? 'array';
        $this->default = $default;

        $cacheProvider = new CacheProviderDecorator(new ArrayCache());
        $this->wrappedHandler = new DoctrineCacheHandler($cacheProvider, $name);
    }

    public function fetch(string $id)
    {
        return $this->wrappedHandler->fetch($id);
    }

    public function save(string $id, $data, ?int $lifeTime = null, array $tags = []): void
    {
        self::$lifeTime = $lifeTime;

        $this->wrappedHandler->save($id, $data, $lifeTime, $tags);
    }

    public function contains(string $id, array $tags = []): bool
    {
        return $this->wrappedHandler->contains($id);
    }

    public function invalidateTags(array $tags): void
    {
        $this->wrappedHandler->invalidateTags($tags);
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
