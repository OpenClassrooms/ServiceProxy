<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Impl;

use OpenClassrooms\ServiceProxy\Attribute\Event;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Config\EventInterceptorConfig;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\Event\EventFactory;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Model\Request\Instance;
use OpenClassrooms\ServiceProxy\Model\Request\Moment;
use OpenClassrooms\ServiceProxy\Model\Response\Response;

final class EventInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    public function __construct(
        private readonly EventFactory $eventFactory,
        private readonly EventInterceptorConfig $config,
        iterable $handlers = [],
    ) {
        parent::__construct($handlers);
    }

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
            ->getAttributesInstances(Event::class);

        foreach ($attributes as $attribute) {
            $handlers = $this->getHandlers(EventHandler::class, $attribute);
            $event = $this->eventFactory->createFromSenderInstance(
                $instance,
                Moment::PREFIX,
                $attribute->name,
                $this->config->eventInstanceClassName,
            );
            foreach ($handlers as $handler) {
                if ($attribute->isPre()) {
                    $handler->dispatch($event, $attribute->queue);
                }
            }
        }

        return new Response();
    }

    public function suffix(Instance $instance): Response
    {
        $attributes = $instance->getMethod()
            ->getAttributes(Event::class);

        foreach ($attributes as $attribute) {
            $attribute = $attribute->newInstance();
            $handlers = $this->getHandlers(EventHandler::class, $attribute);
            foreach ($handlers as $handler) {
                if ($attribute->isPost() && !$instance->getMethod()->threwException()) {
                    $event = $this->eventFactory->createFromSenderInstance(
                        $instance,
                        Moment::SUFFIX,
                        $attribute->name,
                        $this->config->eventInstanceClassName,
                    );
                    $handler->dispatch($event, $attribute->queue);
                }

                if ($attribute->isOnException() && $instance->getMethod()->threwException()) {
                    $event = $this->eventFactory->createFromSenderInstance(
                        $instance,
                        Moment::EXCEPTION,
                        $attribute->name,
                        $this->config->eventInstanceClassName,
                    );
                    $handler->dispatch($event, $attribute->queue);
                }
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
            ->hasAttribute(Event::class);
    }
}
