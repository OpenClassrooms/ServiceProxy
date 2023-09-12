<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Event\Listen;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Response\Response;

final class ListenInterceptor extends AbstractInterceptor implements StartUpInterceptor
{
    public function startUp(Instance $instance): Response
    {
        $attributes = $instance->getMethod()
            ->getAttributesInstances(Listen::class);

        foreach ($attributes as $attribute) {
            $handlers = $this->getHandlers(EventHandler::class, $attribute);
            foreach ($handlers as $handler) {
                $handler->listen($instance, $attribute->name, $attribute->priority);
            }
        }

        return new Response();
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
