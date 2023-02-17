<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract;

use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

interface SuffixInterceptor
{
    public function suffix(Instance $instance): Response;

    public function supportsSuffix(Instance $instance): bool;
}
