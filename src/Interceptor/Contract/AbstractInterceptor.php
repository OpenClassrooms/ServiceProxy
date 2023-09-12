<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract;

use OpenClassrooms\ServiceProxy\Annotation\Annotation;
use OpenClassrooms\ServiceProxy\Handler\Contract\AnnotationHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\DuplicatedHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\HandlerNotFound;
use OpenClassrooms\ServiceProxy\Handler\Exception\MissingDefaultHandler;

abstract class AbstractInterceptor
{
    /**
     * @var array<class-string<AnnotationHandler>, array<string, AnnotationHandler>>
     */
    private array $handlers;

    /**
     * @param AnnotationHandler[] $handlers
     */
    public function __construct(iterable $handlers = [])
    {
        $this->setHandlers($handlers);
    }

    /**
     * @param AnnotationHandler[] $handlers
     */
    final public function setHandlers(iterable $handlers): void
    {
        $handlers = $this->indexHandlers($handlers);
        $this->checkMultipleHandlersWithNoDefault($handlers);
        $this->handlers = $handlers;
    }

    /**
     * @template T of AnnotationHandler
     *
     * @param class-string<T> $handlerInterface
     *
     * @return T[]
     */
    final public function getHandlers(string $handlerInterface, Annotation $annotation): array
    {
        $annotationClass = \get_class($annotation);
        $handlers = $this->handlers[$handlerInterface] ?? [];
        if (\count($annotation->getHandlers()) === 0) {
            if (\count($handlers) === 1) {
                // @phpstan-ignore-next-line
                return array_values($handlers);
            }
            if (\count($handlers) > 1) {
                // @phpstan-ignore-next-line
                return array_values(
                    array_filter($handlers, static fn (AnnotationHandler $handler) => $handler->isDefault())
                );
            }
        }

        $foundHandlers = [];
        foreach ($annotation->getHandlers() as $handlerName) {
            if (!isset($handlers[$handlerName])) {
                throw new HandlerNotFound("No handler '{$handlerName}' found for annotation {$annotationClass}");
            }
            $foundHandlers[$handlerName] = $handlers[$handlerName];
        }

        if (\count($foundHandlers) > 0) {
            // @phpstan-ignore-next-line
            return array_values($foundHandlers);
        }

        throw new HandlerNotFound("No handler found for annotation {$annotationClass}");
    }

    /**
     * @param AnnotationHandler[] $handlers
     *
     * @return array<class-string<AnnotationHandler>, array<string, AnnotationHandler>>
     */
    private function indexHandlers(iterable $handlers): array
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
     * @return class-string<AnnotationHandler>
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
    private function checkMultipleHandlersWithNoDefault(array $handlers): void
    {
        foreach ($handlers as $handlerInterface => $handlersByInterface) {
            if (\count($handlersByInterface) > 1) {
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
