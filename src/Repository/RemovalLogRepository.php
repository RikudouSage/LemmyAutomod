<?php

namespace App\Repository;

use App\Entity\RemovalLog;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RemovalLog>
 *
 * @method RemovalLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method RemovalLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method RemovalLog[]    findAll()
 * @method RemovalLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemovalLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RemovalLog::class);
    }

    /**
     * @return array<RemovalLog>
     */
    public function findOlderThan(DateTimeInterface $dateTime): array
    {
        return $this->createQueryBuilder('rl')
            ->andWhere('rl.validUntil < :date')
            ->setParameter('date', $dateTime)
            ->getQuery()
            ->getResult()
        ;
    }
}
