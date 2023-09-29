<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

interface CacheHandler extends AttributeHandler
{
    /**
     * @return mixed
     */
    public function fetch(string $id);

    /**
     * @param array<int, string> $tags
     * @param mixed              $data
     */
    public function save(string $id, $data, ?int $lifeTime = null, array $tags = []): void;

    public function contains(string $id): bool;

    /**
     * @param array<int, string> $tags
     */
    public function invalidateTags(array $tags): void;
}
