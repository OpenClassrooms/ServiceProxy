<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Tests\Double\Mock\Lock;

use OpenClassrooms\ServiceProxy\Handler\Contract\LockHandler;

class LockHandlerMock implements LockHandler
{
    private array $locks = [];

    private array $locksHistory = [];

    public function __construct()
    {
    }

    public function acquire(string $key): void
    {
        $this->locks[$key] = true;
        $this->locksHistory[$key] = true;
    }

    public function getName(): string
    {
        return 'lock_mock';
    }

    public function hasAcquired(string $key): bool
    {
        return isset($this->locksHistory[$key]);
    }

    public function isAcquired(string $key): bool
    {
        return isset($this->locks[$key]);
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function release(string $key): void
    {
        unset($this->locks[$key]);
    }

    public function setDefaultHandlers(array $defaultHandlers): void
    {
    }
}
