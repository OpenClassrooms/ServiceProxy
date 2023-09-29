<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Contract;

use OpenClassrooms\ServiceProxy\Annotation\Exception\InvalidEventNameException;

/**
 * @template T of object
 */
interface EventHandler extends AttributeHandler
{
    /**
     * @param array<string, mixed> $parameters
     * @param mixed $response
     *
     * @return T
     * @throws InvalidEventNameException
     */
    public function make(
        string $eventName,
        string $senderClassShortName,
        ?array $parameters = null,
        $response = null,
        \Exception $exception = null
    );

    /**
     * @param T $event
     */
    public function send(object $event): void;
}
