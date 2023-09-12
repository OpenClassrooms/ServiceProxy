<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Impl\Transaction;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use OpenClassrooms\ServiceProxy\Handler\Contract\TransactionHandler;
use OpenClassrooms\ServiceProxy\Handler\Impl\ConfigurableHandler;

final class DoctrineOrmTransactionHandler implements TransactionHandler
{
    use ConfigurableHandler;

    /**
     * @var array<string, EntityManager>
     */
    private array $entityManagers;

    public function __construct(ManagerRegistry $doctrineRegistry)
    {
        /** @var array<string, EntityManager> $managers */
        $managers = $doctrineRegistry->getManagers();
        $this->entityManagers = $managers;
    }

    public function begin(): bool
    {
        foreach ($this->entityManagers as $entityManager) {
            $entityManager->beginTransaction();
        }

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function commit(): bool
    {
        foreach ($this->entityManagers as $entityManager) {
            $entityManager->flush();
            if ($entityManager->getConnection()->isTransactionActive()) {
                $entityManager->commit();
            }
        }

        return true;
    }

    public function rollback(): bool
    {
        foreach ($this->entityManagers as $entityManager) {
            if ($entityManager->getConnection()->isTransactionActive()) {
                $entityManager->rollback();
            }
        }

        return true;
    }

    public function getName(): string
    {
        return $this->name ?? 'doctrine_orm';
    }
}
