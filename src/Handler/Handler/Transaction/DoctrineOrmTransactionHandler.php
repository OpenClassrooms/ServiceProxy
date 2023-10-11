<?php

declare(strict_types=1);

namespace OpenClassrooms\ServiceProxy\Handler\Handler\Transaction;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use OpenClassrooms\ServiceProxy\Handler\Contract\TransactionHandler;
use OpenClassrooms\ServiceProxy\Handler\Handler\ConfigurableHandler;

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

    /**
     * @param string[] $entityManagers
     */
    public function begin(array $entityManagers): bool
    {
        foreach ($this->entityManagers as $name => $entityManager) {
            if (!\in_array($name, $entityManagers, true)) {
                continue;
            }
            $entityManager->beginTransaction();
        }

        return true;
    }

    /**
     * @param string[] $entityManagers
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function commit(array $entityManagers): bool
    {
        foreach ($this->entityManagers as $name => $entityManager) {
            if (!\in_array($name, $entityManagers, true)) {
                continue;
            }
            $entityManager->flush();
            if ($entityManager->getConnection()->isTransactionActive()) {
                $entityManager->commit();
            }
        }

        return true;
    }

    /**
     * @param string[] $entityManagers
     */
    public function rollback(array $entityManagers): bool
    {
        foreach ($this->entityManagers as $name => $entityManager) {
            if (!\in_array($name, $entityManagers, true)) {
                continue;
            }
            if ($entityManager->getConnection()->isTransactionActive()) {
                $entityManager->rollback();
            }
        }

        return true;
    }

    public function getName(): string
    {
        return 'doctrine_orm';
    }
}
