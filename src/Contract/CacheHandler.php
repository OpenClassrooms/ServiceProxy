<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Contract;

interface CacheHandler extends AnnotationHandler
{
    /**
     * @return mixed|false
     */
    public function fetchWithNamespace(string $id, ?string $namespaceId = null);

    public function saveWithNamespace(string $id, $data, ?string $namespaceId = null, $lifeTime = null): bool;

    public function contains(string $id): bool;

    public function fetch(string $id);

    public function save(string $id, $data, ?int $lifeTime = null): bool;
}
