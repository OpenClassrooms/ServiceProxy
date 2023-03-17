<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\Cache\SymfonyCacheHandler;
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
        $this->name = $name ?? 'array_mock';
        $this->default = $default;

        $adapter = new TagAwareAdapter(new ArrayAdapter());
        $this->wrappedHandler = new SymfonyCacheHandler($adapter, $name);
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

    public function contains(string $id): bool
    {
        return $this->wrappedHandler->contains($id);
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function invalidateTags(array $tags): bool
    {
        return $this->wrappedHandler->invalidateTags($tags);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
