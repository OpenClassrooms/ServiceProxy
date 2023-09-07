<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Invoker\Contract;

use OpenClassrooms\ServiceProxy\Model\Request\Instance;

interface MethodInvoker
{
    /**
     * @throws \InvalidArgumentException
     */
    public function invoke(Instance $instance, ?object $object = null): mixed;
}
