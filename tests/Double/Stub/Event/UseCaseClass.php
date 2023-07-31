<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

class UseCaseClass implements UseCase
{
    public function execute($parameters): int
    {
        return 1;
    }
}
