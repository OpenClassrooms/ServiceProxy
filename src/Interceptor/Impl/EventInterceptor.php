<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Event;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Context;
use OpenClassrooms\ServiceProxy\Interceptor\Request\ContextType;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

final class EventInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    public function getPrefixPriority(): int
    {
        return 20;
    }

    public function getSuffixPriority(): int
    {
        return 10;
    }

    public function prefix(Instance $instance): Response
    {
        $attributes = $instance->getMethod()
            ->getAttributesInstances(Event::class)
        ;

        foreach ($attributes as $attribute) {
            $handler = $this->getHandler(EventHandler::class, $attribute);
            if ($attribute->isPre()) {
                $instance->setContext(new Context(ContextType::PREFIX, $attribute));
                $handler->dispatch($instance);
            }
        }

        return new Response();
    }

    public function suffix(Instance $instance): Response
    {
        $attributes = $instance->getMethod()
            ->getAttributes(Event::class)
        ;

        foreach ($attributes as $attribute) {
            $attribute = $attribute->newInstance();
            $handler = $this->getHandler(EventHandler::class, $attribute);

            if ($attribute->isPost() && !$instance->getMethod()->threwException()) {
                $instance->setContext(new Context(ContextType::SUFFIX, $attribute));
                $handler->dispatch($instance);
            }

            if ($attribute->isOnException() && $instance->getMethod()->threwException()) {
                $instance->setContext(new Context(ContextType::EXCEPTION, $attribute));
                $handler->dispatch($instance);
            }
        }

        return new Response();
    }

    public function supportsSuffix(Instance $instance): bool
    {
        return $this->supportsPrefix($instance);
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAttribute(Event::class)
        ;
    }
}
