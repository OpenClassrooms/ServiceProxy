<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Contract;

use OpenClassrooms\ServiceProxy\Annotation\Annotation;
use OpenClassrooms\ServiceProxy\Attribute\Attribute;
use OpenClassrooms\ServiceProxy\Handler\Contract\AttributeHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\DuplicatedDefaultHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\DuplicatedHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\HandlerNotFound;
use OpenClassrooms\ServiceProxy\Handler\Exception\MissingDefaultHandler;

abstract class AbstractInterceptor
{
    /**
     * @var array<class-string<AttributeHandler>, array<string, AttributeHandler>>
     */
    private array $handlers;

    /**
     * @param AttributeHandler[] $handlers
     */
    public function __construct(iterable $handlers = [])
    {
        $this->setHandlers($handlers);
    }

    /**
     * @param AttributeHandler[] $handlers
     */
    final public function setHandlers(iterable $handlers): void
    {
        $handlers = $this->indexHandlers($handlers);

        $this->checkDuplicateDefaults($handlers);
        $this->checkMultipleHandlersWithNoDefault($handlers);

        $this->handlers = $handlers;
    }

    /**
     * @template T of AttributeHandler
     *
     * @param class-string<T> $handlerInterface
     *
     * @return T
     */
    final public function getHandler(string $handlerInterface, Annotation|Attribute $attribute): AttributeHandler
    {
        $handlers = $this->handlers[$handlerInterface] ?? [];
        if ($attribute->getHandler() === null) {
            if (\count($handlers) === 1) {
                // @phpstan-ignore-next-line
                return array_values($handlers)[0];
            }
            if (\count($handlers) > 1) {
                foreach ($handlers as $handler) {
                    if ($handler->isDefault()) {
                        // @phpstan-ignore-next-line
                        return $handler;
                    }
                }
            }
        }

        $handlerName = $attribute->getHandler();
        if (isset($handlers[$handlerName])) {
            // @phpstan-ignore-next-line
            return $handlers[$handlerName];
        }

        $handlerName = $handlerName ?? 'default';
        $attributeClass = \get_class($attribute);
        $type = $attribute instanceof Attribute ? "attribute" : "annotation";
        throw new HandlerNotFound(
            "No handler found for {$type} {$attributeClass} with name {$handlerName}"
        );
    }

    /**
     * @param AttributeHandler[] $handlers
     *
     * @return array<class-string<AttributeHandler>, array<string, AttributeHandler>>
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
     * @return class-string<AttributeHandler>
     */
    private function getHandlerInterface(AttributeHandler $handler): string
    {
        $interfaces = class_implements($handler);
        foreach ($interfaces as $interface) {
            if (is_subclass_of($interface, AttributeHandler::class)) {
                return $interface;
            }
        }

        throw new \InvalidArgumentException(
            'All handlers must implement AttributeHandler interface.'
        );
    }

    /**
     * @param array<class-string<AttributeHandler>, array<string, AttributeHandler>> $handlers
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

    /**
     * @param array<class-string<AttributeHandler>, array<string, AttributeHandler>> $handlers
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
