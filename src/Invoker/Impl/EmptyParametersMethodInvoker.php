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
    public function invoke(Instance $instance, ?object $object = null): array
    {
        if ($instance->getMethod()->getReflection()->getNumberOfRequiredParameters() !== 0) {
            throw new \InvalidArgumentException();
        }

        return [];
    }
}
