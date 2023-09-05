<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract;

use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

interface StartUpInterceptor
{
    public const PREFIX_TYPE = 'startUp';

    public function startUp(Instance $instance): Response;

    public function supportsStartUp(Instance $instance): bool;

    public function getStartUpPriority(): int;
}
