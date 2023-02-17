<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Interceptor\Response;

final class Response
{
    private bool $earlyReturn;

    /**
     * @var mixed
     */
    private $value;

    public function __construct($value = null, $earlyReturn = false)
    {
        $this->value = $value;
        $this->earlyReturn = $earlyReturn;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isEarlyReturn(): bool
    {
        return $this->earlyReturn;
    }
}
