<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Stub\Event;

use OpenClassrooms\ServiceProxy\Annotation\Event;

class ClassImplementingUseCaseInterface implements UseCase
{
    /**
     * @Event()
     */
    public function execute($parameters): int
    {
        return 1;
    }
}
