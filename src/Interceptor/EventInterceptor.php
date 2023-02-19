<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor;

use OpenClassrooms\ServiceProxy\Annotations\Event;
use OpenClassrooms\ServiceProxy\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

class EventInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    /**
     * @throws \OpenClassrooms\ServiceProxy\Annotations\Exceptions\InvalidEventNameException
     */
    public function prefix(Instance $instance): Response
    {
        $annotations = $instance->getMethod()->getAnnotations(Event::class);

        foreach ($annotations as $annotation) {
            $handler = $this->getHandler(EventHandler::class, $annotation);
            if ($annotation->hasMethod(Event::PRE_METHOD)) {
                $event = $handler->make(
                    $this->getEventName($instance, $annotation, Event::PRE_METHOD),
                    $instance->getMethod()->getParameters()
                );
                $handler->send($event);
            }
        }

        return new Response();
    }

    private function getEventName(Instance $instance, Event $annotation, string $type): string
    {
        $name = $annotation->getName();
        if (null !== $name) {
            return $name;
        }

        $name = $instance->getReflection()->getShortName();
        if (!$annotation->isUseClassNameOnly()) {
            $name = $instance->getMethod()->getName() . '.' . $name;
        }
        $name = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));

        $prefix = $annotation->getDefaultPrefix();
        $type = $type === Event::ON_EXCEPTION_METHOD ? 'exception' : $type;

        return "$prefix.$type.$name";
    }

    /**
     * @throws \OpenClassrooms\ServiceProxy\Annotations\Exceptions\InvalidEventNameException
     */
    public function suffix(Instance $instance): Response
    {
        $annotations = $instance->getMethod()->getAnnotations(Event::class);

        foreach ($annotations as $annotation) {
            $handler = $this->getHandler(EventHandler::class, $annotation);

            if ($annotation->hasMethod(Event::POST_METHOD) && !$instance->getMethod()->threwException()) {
                $event = $handler->make(
                    $this->getEventName($instance, $annotation, Event::POST_METHOD),
                    $instance->getMethod()->getParameters(),
                    $instance->getMethod()->getReturnedValue(),
                );
                $handler->send($event);
            }

            if ($annotation->hasMethod(Event::ON_EXCEPTION_METHOD) && $instance->getMethod()->threwException()) {
                $event = $handler->make(
                    $this->getEventName($instance, $annotation, Event::ON_EXCEPTION_METHOD),
                    $instance->getMethod()->getParameters(),
                    null,
                    $instance->getMethod()->getException()
                );
                $handler->send($event);
            }
        }

        return new Response();
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()->hasAnnotation(Event::class);
    }

    public function supportsSuffix(Instance $instance): bool
    {
        return $instance->getMethod()->hasAnnotation(Event::class);
    }
}
