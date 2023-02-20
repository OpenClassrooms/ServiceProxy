<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Contract;

interface CacheHandler extends AnnotationHandler
{
    /**
     * @return mixed|false
     */
    public function fetchWithNamespace(string $id, ?string $namespaceId = null);

    /**
     * @param mixed $data
     */
    public function saveWithNamespace(string $id, $data, ?string $namespaceId = null, ?int $lifeTime = null): bool;

    public function contains(string $id): bool;

    /**
     * @return mixed
     */
    public function fetch(string $id);

    /**
     * @param mixed $data
     */
    public function save(string $id, $data, ?int $lifeTime = null): bool;
}
