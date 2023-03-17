<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Cache;

use Doctrine\Common\Cache\ArrayCache;
use OpenClassrooms\ServiceProxy\Handler\Contract\CacheHandler;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final class CacheHandlerMock implements CacheHandler
{
    public static ?int $lifeTime;

    private ArrayAdapter $cacheAdapter;

    private bool $default;

    private string $name;

    public function __construct(?string $name = null, bool $default = true)
    {
        $this->name = $name ?? 'array';
        $this->cacheAdapter = new ArrayAdapter();

        self::$lifeTime = null;
        $this->default = $default;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function fetch(string $id)
    {
        return $this->cacheAdapter->getItem($id)->get();
    }

    public function save(string $id, $data, ?int $lifeTime = null, array $tags = []): void
    {
        self::$lifeTime = $lifeTime;

        $this->cacheAdapter->get($id, function (ItemInterface $item) use ($tags, $data, $lifeTime) {
            $item->expiresAfter($lifeTime);

            return $data;
        });
    }

    public function contains(string $id): bool
    {
        return $this->cacheAdapter->hasItem($id);
    }

    public function isDefault(): bool
    {
        return $this->default;
    }
}
