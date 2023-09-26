<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

interface CacheHandler extends AnnotationHandler
{
    /**
     * @return mixed
     */
    public function fetch(string $poolName, string $id);

    /**
     * @param array<int, string> $pools
     * @param array<int, string> $tags
     * @param mixed              $data
     */
    public function save(array $pools, string $id, $data, ?int $lifeTime = null, array $tags = []): void;

    public function contains(string $poolName, string $id): bool;

    /**
     * @param array<int, string> $pools
     * @param array<int, string> $tags
     */
    public function invalidateTags(array $pools, array $tags): void;
}
