<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\Cache\SymfonyCacheHandler;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

final class CacheHandlerMock implements CacheHandler
{
    public static ?int $lifeTime = null;

    private string $name;

    private bool $default;

    private CacheHandler $wrappedHandler;

    public function __construct(?string $name = null, bool $default = true, ?string $directory = null)
    {
        $this->name = $name ?? 'array';
        $this->default = $default;

        $this->wrappedHandler = new SymfonyCacheHandler([
            'default' => new TagAwareAdapter(new FilesystemAdapter('', 0, $directory)),
            'foo' => new TagAwareAdapter(new FilesystemAdapter('', 0, $directory)),
            'bar' => new TagAwareAdapter(new FilesystemAdapter('', 0, $directory)),
        ], $name);
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

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function invalidateTags(string $poolName, array $tags): void
    {
        $this->wrappedHandler->invalidateTags($poolName, $tags);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDefaultHandlers(array $defaultHandlers): void
    {
    }
}
