<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

use Psr\Cache\CacheItemInterface;

interface CacheHandler extends AnnotationHandler
{
    public function fetch(string $poolName, string $id): CacheItemInterface;

    /**
     * @param array<int, string> $tags
     * @param mixed              $data
     */
    public function save(string $poolName, string $id, $data, ?int $lifeTime = null, array $tags = []): void;

    /**
     * @param array<int, string> $tags
     */
    public function invalidateTags(string $poolName, array $tags): void;
}
