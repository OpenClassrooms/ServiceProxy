<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract;

use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;

interface PrefixInterceptor
{
    public const PREFIX_TYPE = 'prefix';

    /**
     * @template T of object
     *
     * @param Instance<T> $instance
     */
    public function prefix(Instance $instance): Response;

    /**
     * @template T of object
     *
     * @param Instance<T> $instance
     */
    public function supportsPrefix(Instance $instance): bool;

    public function getPrefixPriority(): int;
}
