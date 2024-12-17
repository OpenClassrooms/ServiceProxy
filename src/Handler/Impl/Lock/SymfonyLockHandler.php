<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Lock;

use OpenClassrooms\ServiceProxy\Handler\Contract\LockHandler;
use OpenClassrooms\ServiceProxy\Handler\Exception\LockException;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

final class SymfonyLockHandler implements LockHandler
{
    use ConfigurableHandler;

    /**
     * @var LockInterface[]
     */
    private array $locks = [];

    public function __construct(
        private readonly LockFactory $lockFactory
    ) {
    }

    /**
     * @throws LockException
     */
    public function acquire(string $key): void
    {
        try {
            $this->locks[$key] = $this->lockFactory->createLock($key);
            $this->locks[$key]->acquire(true);
        } catch (LockAcquiringException|LockConflictedException $e) {
            throw new LockException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getName(): string
    {
        return 'symfony_lock';
    }

    /**
     * @throws LockException
     */
    public function release(string $key): void
    {
        if (!isset($this->locks[$key])) {
            return;
        }

        try {
            $this->locks[$key]->release();
        } catch (LockReleasingException $e) {
            throw new LockException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
