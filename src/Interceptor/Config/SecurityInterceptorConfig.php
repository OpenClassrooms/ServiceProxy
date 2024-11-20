<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Config;

final class SecurityInterceptorConfig
{
    public function __construct(
        public readonly bool $bypassSecurity = false,
    ) {
    }
}
