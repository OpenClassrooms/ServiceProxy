<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\Cache\SymfonyCacheHandler;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

final class CacheHandlerMock implements CacheHandler
{
    public static ?int $lifeTime = null;

    private string $name;

    private bool $default;

    private CacheHandler $wrappedHandler;

    public function __construct(?string $name = null, bool $default = true)
    {
        $this->name = $name ?? 'array';
        $this->default = $default;

        $this->wrappedHandler = new SymfonyCacheHandler([
            'default' => new TagAwareAdapter(new ArrayAdapter()),
        ], $name);
    }

    public function fetch(string $poolName, string $id)
    {
        return $this->wrappedHandler->fetch($poolName, $id);
    }

    public function save(array $pools, string $id, $data, ?int $lifeTime = null, array $tags = []): void
    {
        self::$lifeTime = $lifeTime;

        $this->wrappedHandler->save($pools, $id, $data, $lifeTime, $tags);
    }

    public function contains(string $poolName, string $id): bool
    {
        return $this->wrappedHandler->contains($poolName, $id);
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function invalidateTags(array $pools, array $tags): void
    {
        $this->wrappedHandler->invalidateTags($pools, $tags);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDefaultHandlers(array $defaultHandlers): void
    {
    }
}
