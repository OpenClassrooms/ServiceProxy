<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Model\Response;

final class Response
{
    private bool $earlyReturn;

    private mixed $value;

    public function __construct(mixed $value = null, bool $earlyReturn = false)
    {
        $this->value = $value;
        $this->earlyReturn = $earlyReturn;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isEarlyReturn(): bool
    {
        return $this->earlyReturn;
    }
}
