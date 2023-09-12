<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Invoker\Impl;

use OpenClassrooms\ServiceProxy\Invoker\Contract\MethodInvoker;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;

final class AggregateMethodInvoker
{
    /**
     * @param  iterable<MethodInvoker> $invokers
     */
    public function __construct(
        private iterable $invokers
    ) {
    }

    public function invoke(Instance $instance, ?object $object = null): mixed
    {
        if (!\is_array($this->invokers)) {
            $this->invokers = iterator_to_array($this->invokers);
        }

        usort(
            $this->invokers,
            static fn (MethodInvoker $a, MethodInvoker $b) => $a->getPriority() <=> $b->getPriority()
        );

        foreach ($this->invokers as $invoker) {
            try {
                return $invoker->invoke($instance, $object);
            } catch (\InvalidArgumentException) {
                continue;
            }
        }

        $methodName = $instance->getReflection()
            ->getName() . '::' . $instance->getMethod()->getName();
        $message = "No invoker found for method {$methodName}";
        if ($object !== null) {
            $objectType = \get_class($object);
            $message .= " on object {$objectType}";
        }

        throw new \RuntimeException($message);
    }
}
