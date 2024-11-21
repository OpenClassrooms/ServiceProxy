<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use OpenClassrooms\DoctrineCacheExtension\CacheProviderDecorator;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\Cache\DoctrineCacheHandler;
use OpenClassrooms\ServiceProxy\Util\ArrayCache;
use Psr\Cache\CacheItemInterface;

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

    public function fetch(string $poolName, string $id): CacheItemInterface
    {
        return $this->wrappedHandler->fetch($poolName, $id);
    }

    public function save(string $poolName, string $id, $data, ?int $lifeTime = null, array $tags = []): void
    {
        self::$lifeTime = $lifeTime;

        $this->wrappedHandler->save($poolName, $id, $data, $lifeTime, $tags);
    }

    public function invalidateTags(string $poolName, array $tags): void
    {
        $this->wrappedHandler->invalidateTags($poolName, $tags);
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDefaultHandlers(array $defaultHandlers): void
    {
    }
}
