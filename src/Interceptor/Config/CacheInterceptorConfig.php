<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Config;

final class CacheInterceptorConfig
{

    /**
     * @param  array<class-string> $autoTagsExcludedClasses
     */
    public function __construct(
        public readonly array $autoTagsExcludedClasses = [],
    ) {
    }
}
