<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Event\Listen;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\StartUpInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

final class ListenInterceptor extends AbstractInterceptor implements StartUpInterceptor
{
    public function startUp(Instance $instance): Response
    {
        $attribute = $instance->getMethod()
            ->getAttribute(Listen::class);
        $handler = $this->getHandler(EventHandler::class, $attribute);
        $handler->listen($instance);

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
