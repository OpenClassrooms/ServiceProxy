<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor;

use OpenClassrooms\ServiceProxy\Annotations\Annotation;
use OpenClassrooms\ServiceProxy\Annotations\Exceptions\InvalidHandlerName;
use OpenClassrooms\ServiceProxy\Contract\AnnotationHandler;

abstract class AbstractInterceptor
{
    /**
     * @var \OpenClassrooms\ServiceProxy\Contract\AnnotationHandler[]
     */
    private array $handlers;

    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    /**
     * @template T of class-string<\OpenClassrooms\ServiceProxy\Contract\AnnotationHandler>
     *
     * @param class-string<T> $handlerClass
     *
     * @return T
     */
    public function getHandler(string $handlerClass, Annotation $annotation): AnnotationHandler
    {
        $annotationClass = get_class($annotation);

        if ($annotation->getHandler() === null) {
            if (count($this->handlers) === 1) {
                return $this->handlers[0];
            }
            if (count($this->handlers) > 1) {
                throw new InvalidHandlerName(
                    "Multiple handlers found for annotation $annotationClass, you must specify a handler name"
                );
            }
            return $this->handlers[0];
        }

        foreach ($this->handlers as $handler) {
            if (
                $handlerClass === $annotation->getHandlerClass()
                && $handler->getName() === $annotation->getHandler()
            ) {
                return $handler;
            }
        }

        throw new InvalidHandlerName(
            "No handler found for annotation $annotationClass with name {$annotation->getHandler()}"
        );
    }
}
