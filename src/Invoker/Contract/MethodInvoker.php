<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Invoker\Contract;

use OpenClassrooms\ServiceProxy\Model\Request\Instance;

interface MethodInvoker
{
    /**
     * @throws \InvalidArgumentException
     */
    public function invoke(Instance $listenerInstance, ?object $event = null): mixed;

    public function getPriority(): int;
}
