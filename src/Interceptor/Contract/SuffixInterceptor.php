<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract;

use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;

interface SuffixInterceptor
{
    public const SUFFIX_TYPE = 'suffix';

    /**
     * @template T of object
     *
     * @param Instance<T> $instance
     */
    public function suffix(Instance $instance): Response;

    /**
     * @template T of object
     *
     * @param Instance<T> $instance
     */
    public function supportsSuffix(Instance $instance): bool;

    public function getSuffixPriority(): int;
}
