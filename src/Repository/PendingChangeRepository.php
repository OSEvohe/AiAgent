<?php

namespace App\Repository;

use App\Entity\PendingChange;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PendingChange>
 */
class PendingChangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PendingChange::class);
    }

    public function save(PendingChange $pendingChange): void
    {
        $this->getEntityManager()->persist($pendingChange);
        $this->getEntityManager()->flush();
    }
}

