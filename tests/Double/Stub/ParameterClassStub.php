<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub;

class ParameterClassStub
{
    public int $publicField = 1;

    private int $privateField = 2;

    public function getPrivateField(): int
    {
        return $this->privateField;
    }
}
