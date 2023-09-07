<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Invoker\Impl;

use OpenClassrooms\ServiceProxy\Invoker\Contract\MethodInvoker;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;

final class AggregateMethodInvoker implements MethodInvoker
{
    /**
     * @var iterable<MethodInvoker> $invokers
     */
    private iterable $invokers = [];

    public function invoke(Instance $instance, ?object $object = null): mixed
    {
        foreach ($this->invokers as $invoker) {
            try {
                return $invoker->invoke($instance, $object);
            } catch (\InvalidArgumentException) {
                continue;
            }
        }

        $message = "No invoker found for method {$instance->getMethod()
            ->getName()}";
        if ($object !== null) {
            $objectType = \get_class($object);
            $message .= " on object {$objectType}";
        }

        throw new \RuntimeException($message);
    }

    /**
     * @param iterable<MethodInvoker> $invokers
     */
    public function setInvokers(iterable $invokers): void
    {
        $this->invokers = $invokers;
    }
}
