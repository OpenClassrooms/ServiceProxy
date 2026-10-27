<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Attribute\Cache;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
final class Tag
{
    public function __construct(
        public readonly ?string $prefix = null
    ) {
    }
}
