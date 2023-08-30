<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Event;
use OpenClassrooms\ServiceProxy\Annotation\Exception\InvalidEventNameException;
use OpenClassrooms\ServiceProxy\Attribute\Listen;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;
use OpenClassrooms\ServiceProxy\Model\Message\Message;

final class ListenInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    public function getPrefixPriority(): int
    {
        return 20;
    }

    public function getSuffixPriority(): int
    {
        return 10;
    }

    /**
     * @throws InvalidEventNameException
     */
    public function prefix(Instance $instance): Response
    {
        $annotations = $instance->getMethod()
                                ->getAnnotations(Event::class)
        ;

        foreach ($annotations as $annotation) {
            $handler = $this->getHandler(EventHandler::class, $annotation);
            if ($annotation->hasMethod(Event::PRE_METHOD)) {
                $event = new Message(
                    name: $this->getEventName($instance, $annotation, Event::PRE_METHOD),
                    body: [
                              'sender'     => $instance->getReflection()
                                                       ->getShortName(),
                              'parameters' => $instance->getMethod()
                                                       ->getParameters(),
                          ],
                );
                $handler->dispatch($event);
            }
        }

        return new Response();
    }

    private function getEventName(Instance $instance, Event $annotation, string $type): string
    {
        $name = $annotation->getName();
        if ($name !== null) {
            return $name;
        }

        $name = $instance->getReflection()->getShortName();
        if (!$annotation->isUseClassNameOnly()) {
            $name = $instance->getMethod()->getName() . '.' . $name;
        }
        $name = mb_strtolower((string) preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));

        $prefix = $annotation->getDefaultPrefix();
        $type = $type === Event::ON_EXCEPTION_METHOD ? 'exception' : $type;

        return "{$prefix}.{$type}.{$name}";
    }

    /**
     * @throws InvalidEventNameException
     */
    public function suffix(Instance $instance): Response
    {
        $annotations = $instance->getMethod()
                                ->getAnnotations(Event::class)
        ;

        foreach ($annotations as $annotation) {
            $handler = $this->getHandler(EventHandler::class, $annotation);

            if ($annotation->hasMethod(Event::POST_METHOD) && !$instance->getMethod()->threwException()) {
                $event = new Message(
                    name: $this->getEventName($instance, $annotation, Event::POST_METHOD),
                    body: [
                              'sender'     => $instance->getReflection()
                                                       ->getShortName(),
                              'parameters' => $instance->getMethod()
                                                       ->getParameters(),
                              'response'   => $instance->getMethod()
                                                       ->getReturnedValue(),
                          ],
                );
                $handler->dispatch($event);
            }

            if ($annotation->hasMethod(Event::ON_EXCEPTION_METHOD) && $instance->getMethod()->threwException()) {
                $event = new Message(
                    name: $this->getEventName($instance, $annotation, Event::ON_EXCEPTION_METHOD),
                    body: [
                              'sender'     => $instance->getReflection()
                                                       ->getShortName(),
                              'parameters' => $instance->getMethod()
                                                       ->getParameters(),
                              'exception'  => $instance->getMethod()
                                                       ->getException(),
                          ],
                );
                $handler->dispatch($event);
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
                        ->hasAnnotation(Listen::class)
        ;
    }
}
