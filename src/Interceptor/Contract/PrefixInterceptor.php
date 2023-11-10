<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract;

use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;

interface PrefixInterceptor
{
    public const PREFIX_TYPE = 'prefix';

    public function prefix(Instance $instance): Response;

    public function supportsPrefix(Instance $instance): bool;

    public function getPrefixPriority(): int;
}
