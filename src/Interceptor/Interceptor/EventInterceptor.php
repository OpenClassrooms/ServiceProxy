<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Event;
use OpenClassrooms\ServiceProxy\Annotation\Exception\InvalidEventNameException;
use OpenClassrooms\ServiceProxy\Handler\Contract\EventHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\Event\GenericEventHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\Event\SymfonyDispatcherEventHandler;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\AbstractInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\PrefixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Contract\SuffixInterceptor;
use OpenClassrooms\ServiceProxy\Interceptor\Request\Instance;
use OpenClassrooms\ServiceProxy\Interceptor\Response\Response;

final class EventInterceptor extends AbstractInterceptor implements SuffixInterceptor, PrefixInterceptor
{
    public function __construct(
        iterable $handlers = [],
        private ?GenericEventHandler $genericEventHandler = null,
    ) {
        parent::__construct($handlers);
    }

    /**
     * @throws InvalidEventNameException
     */
    public function prefix(Instance $instance): Response
    {
        $annotations = $instance->getMethod()
            ->getAnnotations(Event::class);

        foreach ($annotations as $annotation) {
            $handler = $this->getHandler(EventHandler::class, $annotation);
            if ($annotation->hasMethod(Event::PRE_METHOD)) {
                $event = $handler->make(
                    $this->getEventName($instance, $annotation, Event::PRE_METHOD),
                    $instance->getMethod()
                        ->getParameters()
                );
                $handler->send($event);
            }
        }

        return new Response();
    }

    /**
     * @throws InvalidEventNameException
     */
    public function suffix(Instance $instance): Response
    {
        try {
            $annotations = $instance->getMethod()
                ->getAnnotations(Event::class);

            foreach ($annotations as $annotation) {
                $handler = $this->getHandler(EventHandler::class, $annotation);

                if ($annotation->hasMethod(Event::POST_METHOD) && !$instance->getMethod()->threwException()) {
                    $event = $handler->make(
                        $this->getEventName($instance, $annotation, Event::POST_METHOD),
                        $instance->getMethod()
                            ->getParameters(),
                        $instance->getMethod()
                            ->getReturnedValue(),
                    );
                    $handler->send($event);
                }

                if ($annotation->hasMethod(Event::ON_EXCEPTION_METHOD) && $instance->getMethod()->threwException()) {
                    $event = $handler->make(
                        $this->getEventName($instance, $annotation, Event::ON_EXCEPTION_METHOD),
                        $instance->getMethod()
                            ->getParameters(),
                        null,
                        $instance->getMethod()
                            ->getException()
                    );
                    $handler->send($event);
                }

            }
        } catch (\LogicException) {

        }

        $this->sendPostExecutionGenericEvent($instance);

        return new Response();
    }

    public function supportsPrefix(Instance $instance): bool
    {
        return $instance->getMethod()
            ->hasAnnotation(Event::class);
    }

    public function supportsSuffix(Instance $instance): bool
    {
        if ($this->supportsPrefix($instance)) {
            return true;
        }

        $useCaseInstance = array_filter(
            $instance->getReflection()->getInterfaceNames(),
            static fn (string $interfaceName) => str_ends_with($interfaceName, 'UseCase')
        );

        return !empty($useCaseInstance);
    }

    public function getPrefixPriority(): int
    {
        return 20;
    }

    public function getSuffixPriority(): int
    {
        return 10;
    }

    private function getEventName(Instance $instance, Event $annotation, string $type): string
    {
        $name = $annotation->getName();
        if ($name !== null) {
            return $name;
        }

        $name = $instance->getReflection()
            ->getShortName();
        if (!$annotation->isUseClassNameOnly()) {
            $name = $instance->getMethod()
                ->getName() . '.' . $name;
        }
        $name = mb_strtolower((string) preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $name));

        $prefix = $annotation->getDefaultPrefix();
        $type = $type === Event::ON_EXCEPTION_METHOD ? 'exception' : $type;

        return "{$prefix}.{$type}.{$name}";
    }

    private function sendPostExecutionGenericEvent(Instance $instance): void
    {
        $event = $this->genericEventHandler?->make(
            $instance->getReflection()->getShortName(),
            $instance->getMethod()->getParameters(),
            $instance->getMethod()->getReturnedValue(),
        );
        $this->genericEventHandler?->send($event);
    }
}
