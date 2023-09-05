<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Subscriber\Contract;

interface StartupSubscriber
{
    public function startUp(): void;

    public function supportsStartUp(): bool;

    public function getPriority(): int;
}
