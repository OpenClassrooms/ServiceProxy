<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Invoker\Impl;

use OpenClassrooms\ServiceProxy\Invoker\Contract\MethodInvoker;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;

final class EmptyParametersMethodInvoker implements MethodInvoker
{
    /**
     * @return array<mixed>
     */
    public function invoke(Instance $listenerInstance, ?object $event = null): array
    {
        if ($listenerInstance->getMethod()->getReflection()->getNumberOfRequiredParameters() !== 0) {
            throw new \InvalidArgumentException();
        }

        return [];
    }

    public function getPriority(): int
    {
        return -1000;
    }
}
