<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

interface CacheHandler extends AnnotationHandler
{
    /**
     * @param array<int, string> $tags
     *
     * @return mixed|false
     */
    public function fetch(string $id, array $tags = []);

    /**
     * @param array<int, string> $tags
     * @param mixed              $data
     */
    public function save(string $id, $data, array $tags = [], ?int $lifeTime = null): bool;

    /**
     * @param array<int, string> $tags
     */
    public function contains(string $id, array $tags = []): bool;
}
