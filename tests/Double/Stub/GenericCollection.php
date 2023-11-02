<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

/**
 * @template T
 */
class GenericCollection
{
    /**
     * @param T $data
     */
    public object $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array|object $data = [])
    {
        $this->data = (object) $data;
    }
}
