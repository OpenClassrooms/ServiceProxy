<?php

namespace OpenClassrooms\ServiceProxy\Contract;

use OpenClassrooms\ServiceProxy\Annotations\Exceptions\InvalidEventNameException;

interface EventHandler extends AnnotationHandler
{
    /**
     * @return mixed
     * @throws InvalidEventNameException
     */
    public function make(
        string $eventName,
        ?array $parameters = null,
        $response = null,
        \Exception $exception = null
    );

    public function send($event);
}
