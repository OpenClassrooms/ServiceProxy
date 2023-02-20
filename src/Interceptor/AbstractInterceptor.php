<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor;

use OpenClassrooms\ServiceProxy\Annotation\Annotation;
use OpenClassrooms\ServiceProxy\Contract\AnnotationHandler;
use OpenClassrooms\ServiceProxy\Contract\Exception\DuplicatedDefaultHandler;
use OpenClassrooms\ServiceProxy\Contract\Exception\DuplicatedHandler;
use OpenClassrooms\ServiceProxy\Contract\Exception\HandlerNotFound;
use OpenClassrooms\ServiceProxy\Contract\Exception\MissingDefaultHandler;

abstract class AbstractInterceptor
{
    protected int $prefixPriority = 0;

    protected int $suffixPriority = 0;

    /**
     * @var array<class-string<AnnotationHandler>, array<string, AnnotationHandler>>
     */
    private array $handlers;

    /**
     * @param \OpenClassrooms\ServiceProxy\Contract\AnnotationHandler[] $handlers
     */
    public function __construct(array $handlers = [])
    {
        $handlers = $this->indexHandlers($handlers);

        $this->checkDuplicateDefaults($handlers);
        $this->checkMultipleHandlersWithNoDefault($handlers);

        $this->handlers = $handlers;
    }

    /**
     * @template T of class-string<\OpenClassrooms\ServiceProxy\Contract\AnnotationHandler>
     *
     * @param class-string<T> $handlerInterface
     *
     * @return T
     */
    public function getHandler(string $handlerInterface, Annotation $annotation): AnnotationHandler
    {
        $handlers = $this->handlers[$handlerInterface];
        if ($annotation->getHandler() === null) {
            if (count($handlers) === 1) {
                return array_values($handlers)[0];
            }
            if (count($handlers) > 1) {
                foreach ($handlers as $handler) {
                    if ($handler->isDefault()) {
                        return $handler;
                    }
                }
            }
        }

        $handlerName = $annotation->getHandler();
        if (isset($handlers[$handlerName])) {
            return $handlers[$handlerName];
        }

        $annotationClass = get_class($annotation);
        throw new HandlerNotFound(
            "No handler found for annotation {$annotationClass} with name {$annotation->getHandler()}"
        );
    }

    public function getPrefixPriority(): int
    {
        return $this->prefixPriority;
    }

    public function getSuffixPriority(): int
    {
        return $this->suffixPriority;
    }

    /**
     * @return array<class-string<AnnotationHandler>, array<string, AnnotationHandler>>
     */
    private function indexHandlers(array $handlers): array
    {
        $indexedHandlers = [];
        foreach ($handlers as $handler) {
            $handlerInterface = $this->getHandlerInterface($handler);
            $indexedHandlers[$handlerInterface] ??= [];
            if (isset($indexedHandlers[$handlerInterface][$handler->getName()])) {
                throw new DuplicatedHandler(
                    "Handlers must have a unique name. Duplicate found for {$handler->getName()}, type {$handlerInterface}."
                );
            }
            $indexedHandlers[$handlerInterface][$handler->getName()] = $handler;
        }

        return $indexedHandlers;
    }

    /**
     * @return class-string<\OpenClassrooms\ServiceProxy\Contract\AnnotationHandler>
     */
    private function getHandlerInterface(AnnotationHandler $handler): string
    {
        $interfaces = class_implements($handler);
        foreach ($interfaces as $interface) {
            if (is_subclass_of($interface, AnnotationHandler::class)) {
                return $interface;
            }
        }

        throw new \InvalidArgumentException(
            'All handlers must implement AnnotationHandler interface.'
        );
    }

    /**
     * @param array<class-string<AnnotationHandler>, array<string, AnnotationHandler>> $handlers
     */
    private function checkDuplicateDefaults(array $handlers): void
    {
        foreach ($handlers as $handlerInterface => $handlersByInterface) {
            $defaultHandlers = 0;
            foreach ($handlersByInterface as $handler) {
                if ($handler->isDefault()) {
                    $defaultHandlers++;
                }
            }
            if ($defaultHandlers > 1) {
                throw new DuplicatedDefaultHandler("Only one default handler is allowed for {$handlerInterface}.");
            }
        }
    }

    private function checkMultipleHandlersWithNoDefault(array $handlers): void
    {
        foreach ($handlers as $handlerInterface => $handlersByInterface) {
            if (count($handlersByInterface) > 1) {
                $defaultHandlers = 0;
                foreach ($handlersByInterface as $handler) {
                    if ($handler->isDefault()) {
                        $defaultHandlers++;
                    }
                }
                if ($defaultHandlers === 0) {
                    throw new MissingDefaultHandler(
                        "Multiple handlers found for {$handlerInterface}, but no default handler is defined."
                    );
                }
            }
        }
    }
}
