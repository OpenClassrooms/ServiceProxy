<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\Cache\DoctrineCacheHandler;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class DoctrineCacheHandlerMock implements CacheHandler
{
    public static ?int $lifeTime = null;

    private CacheHandler $wrappedHandler;

    private bool $default;

    private string $name;

    public function __construct(?string $name = null, bool $default = true)
    {
        $this->name = $name ?? 'array';
        $this->default = $default;

        $this->wrappedHandler = new DoctrineCacheHandler(new ArrayAdapter(storeSerialized: false), $name);
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
