<?php

namespace App\Repository;

use App\Entity\IgnoredPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IgnoredPost>
 *
 * @method IgnoredPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method IgnoredPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method IgnoredPost[]    findAll()
 * @method IgnoredPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IgnoredPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IgnoredPost::class);
    }

    //    /**
    //     * @return IgnoredPost[] Returns an array of IgnoredPost objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?IgnoredPost
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
