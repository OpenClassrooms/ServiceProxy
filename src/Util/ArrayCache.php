<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Util;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;

final class ArrayCache extends CacheProvider
{
    /**
     * @psalm-var array<string, array{mixed, int|bool}>> $data each element being a tuple of [$data, $expiration], where the expiration is int|bool
     */
    private array $data = [];

    private int $hitsCount = 0;

    private int $missesCount = 0;

    private int $upTime;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->upTime = time();
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        if (!$this->doContains($id)) {
            ++$this->missesCount;

            return false;
        }

        ++$this->hitsCount;

        return $this->data[$id][0];
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id): bool
    {
        if (!isset($this->data[$id])) {
            return false;
        }

        $expiration = $this->data[$id][1];

        if ($expiration && $expiration < time()) {
            $this->doDelete($id);

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id): bool
    {
        unset($this->data[$id]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush(): bool
    {
        $this->data = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats(): ?array
    {
        return [
            Cache::STATS_HITS => $this->hitsCount,
            Cache::STATS_MISSES => $this->missesCount,
            Cache::STATS_UPTIME => $this->upTime,
            Cache::STATS_MEMORY_USAGE => null,
            Cache::STATS_MEMORY_AVAILABLE => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0): bool
    {
        $this->data[$id] = [$data, $lifeTime ? time() + $lifeTime : false];

        return true;
    }
}
