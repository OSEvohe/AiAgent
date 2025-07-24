<?php

namespace App\Repository;

use App\Entity\Discussion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Discussion>
 */
class DiscussionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Discussion::class);
    }

    public function save(Discussion $discussion): void
    {
        $this->getEntityManager()->persist($discussion);
        $this->getEntityManager()->flush();
    }

    public function persist(Discussion $discussion): void
    {
        $this->getEntityManager()->persist($discussion);
    }

    public function remove(Discussion $discussion): void
    {
        $this->getEntityManager()->remove($discussion);
        $this->getEntityManager()->flush();
    }

    public function findByUid(string $discussionUid): ?Discussion
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.uid = :uid')
            ->setParameter('uid', $discussionUid)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
