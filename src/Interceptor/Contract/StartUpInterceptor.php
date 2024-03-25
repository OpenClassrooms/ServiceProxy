<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract;

use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;

interface StartUpInterceptor
{
    public const PREFIX_TYPE = 'startUp';

    /**
     * @template T of object
     *
     * @param Instance<T> $instance
     */
    public function startUp(Instance $instance): Response;

    /**
     * @template T of object
     *
     * @param Instance<T> $instance
     */
    public function supportsStartUp(Instance $instance): bool;

    public function getStartUpPriority(): int;
}
