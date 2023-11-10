<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Event\Listen;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Moment;
use OpenClassrooms\ServiceProxy\Model\Response\Response;

final class ListenInterceptor extends AbstractInterceptor implements StartUpInterceptor
{
    public function startUp(Instance $instance): Response
    {
        $attributes = $instance->getMethod()
            ->getAttributesInstances(Listen::class);

        foreach ($attributes as $attribute) {
            if ($this->isInfinityLoop($attribute->name, $instance->getReflection()->getName())) {
                continue;
            }
            $handlers = $this->getHandlers(EventHandler::class, $attribute);
            foreach ($handlers as $handler) {
                $handler->listen(
                    $instance,
                    $attribute->name,
                    $attribute->transport,
                    $attribute->priority,
                );
            }
        }

        return new Response();
    }

    private function isInfinityLoop(string $eventName, string $className): bool
    {
        $pattern = '#^('.\implode('|', \array_column(Moment::cases(), 'value')).')\.#';
        if (\preg_match($pattern, $eventName) === 1) {
            $eventNameWithoutPrefix = \explode('.', $eventName)[1];
        } else {
            $eventNameWithoutPrefix = \explode('.', $eventName)[0];
        }
        $eventShortName = \str_replace('_', '', \ucwords($eventNameWithoutPrefix, '_'));
        $tmp = \explode('\\', $className);
        $classShortName = \array_pop($tmp);

        return $eventShortName === $classShortName;
    }

    public function supportsStartUp(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAttribute(Listen::class);
    }

    public function getStartUpPriority(): int
    {
        return 0;
    }
}
