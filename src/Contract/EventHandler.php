<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Contract;

use OpenClassrooms\ServiceProxy\Annotation\Exception\InvalidEventNameException;

interface EventHandler extends AnnotationHandler
{
    /**
     * @param array<string, mixed> $parameters
     * @param mixed $response
     *
     * @return mixed
     * @throws InvalidEventNameException
     */
    public function make(
        string $eventName,
        ?array $parameters = null,
        $response = null,
        \Exception $exception = null
    );

    /**
     * @param mixed $event
     */
    public function send($event): void;
}
