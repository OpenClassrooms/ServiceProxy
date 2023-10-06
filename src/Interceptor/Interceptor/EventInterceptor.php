<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Exception\InvalidEventNameException;
use OpenClassrooms\ServiceProxy\Attribute\Event;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

final class EventInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    public const GENERIC_POST_EXECUTE_EVENT_NAME = 'use_case.post.execute';

    /**
     * @throws InvalidEventNameException
     */
    public function prefix(Instance $instance): Response
    {
        $attributes = $instance->getMethod()
            ->getAttributes(Event::class);

        $attributesExecuted = [];
        foreach ($attributes as $attribute) {
            $instantiatedAttribute = $attribute->newInstance();
            $handler = $this->getHandler(EventHandler::class, $instantiatedAttribute);
            if ($instantiatedAttribute->hasMethod(Event::PRE_METHOD)) {
                $eventName = $this->getEventName($instance, $instantiatedAttribute, Event::PRE_METHOD);
                if (\in_array($eventName, $attributesExecuted, true)) {
                    continue;
                }
                $attributesExecuted[] = $eventName;
                $this->sendPreExecuteEvent($handler, $eventName, $instance);
            }
        }

        return new Response();
    }

    /**
     * @throws InvalidEventNameException
     */
    public function suffix(Instance $instance): Response
    {
        $attributes = $instance->getMethod()
            ->getAttributes(Event::class);

        $attributesExecuted = [];
        foreach ($attributes as $attribute) {
            $instantiatedAttribute = $attribute->newInstance();
            $handler = $this->getHandler(EventHandler::class, $instantiatedAttribute);

            if ($instantiatedAttribute->hasMethod(Event::POST_METHOD) && !$instance->getMethod()->threwException()) {
                $eventName = $this->getEventName($instance, $instantiatedAttribute, Event::POST_METHOD);
                if (\in_array($eventName, $attributesExecuted, true)) {
                    continue;
                }
                $attributesExecuted[] = $eventName;
                $this->sendPostExecuteEvent($handler, $eventName, $instance);
            }

            if ($instantiatedAttribute->hasMethod(
                Event::ON_EXCEPTION_METHOD
            ) && $instance->getMethod()->threwException()) {
                $eventName = $this->getEventName($instance, $instantiatedAttribute, Event::ON_EXCEPTION_METHOD);
                if (\in_array($eventName, $attributesExecuted, true)) {
                    continue;
                }
                $attributesExecuted[] = $eventName;
                $this->sendOnExceptionEvent($handler, $eventName, $instance);
            }

            if ($this->isInstanceImplementInterfaceUseCase($instance)) {
                $eventName = self::GENERIC_POST_EXECUTE_EVENT_NAME;
                $attributesExecuted[] = $eventName;
                $this->sendPostExecuteEvent($handler, $eventName, $instance);
            }
        }

        return new Response();
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAttribute(Event::class);
    }

    public function supportsSuffix(Instance $instance): bool
    {
        return $this->supportsPrefix($instance);
    }

    public function getPrefixPriority(): int
    {
        return 20;
    }

    public function getSuffixPriority(): int
    {
        return 10;
    }

    private function getEventName(Instance $instance, Event $attribute, string $type): string
    {
        $name = $attribute->name;
        if ($name !== null) {
            return $name;
        }

        $name = $instance->getReflection()
            ->getShortName();
        if (!$attribute->useClassNameOnly) {
            $name = $instance->getMethod()
                ->getName() . '.' . $name;
        }

        $name = $this->camelCaseToSnakeCase($name);

        $type = $type === Event::ON_EXCEPTION_METHOD ? 'exception' : $type;
        $prefix = $attribute->defaultPrefix;

        return (!\in_array($prefix, [null, '', false], true) ? "{$prefix}." : '') . "{$type}.{$name}";
    }

    private function isInstanceImplementInterfaceUseCase(Instance $instance): bool
    {
        $useCaseInstance = array_filter(
            $instance->getReflection()
                ->getInterfaceNames(),
            static fn (string $interfaceName) => str_ends_with($interfaceName, 'UseCase')
        );

        return \count($useCaseInstance) === 1;
    }

    private function sendPostExecuteEvent(EventHandler $handler, string $eventName, Instance $instance): void
    {
        $event = $handler->make(
            $eventName,
            $instance->getReflection()
                ->getShortName(),
            $instance->getMethod()
                ->getParameters(),
            $instance->getMethod()
                ->getReturnedValue(),
        );
        $handler->send($event);
    }

    private function sendPreExecuteEvent(EventHandler $handler, string $eventName, Instance $instance): void
    {
        $event = $handler->make(
            $eventName,
            $instance->getReflection()
                ->getShortName(),
            $instance->getMethod()
                ->getParameters()
        );
        $handler->send($event);
    }

    private function sendOnExceptionEvent(EventHandler $handler, string $eventName, Instance $instance): void
    {
        $event = $handler->make(
            $eventName,
            $instance->getReflection()
                ->getShortName(),
            $instance->getMethod()
                ->getParameters(),
            null,
            $instance->getMethod()
                ->getException()
        );
        $handler->send($event);
    }

    private function camelCaseToSnakeCase(string $name): string|array|null|false
    {
        return mb_strtolower((string) preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));
    }
}
