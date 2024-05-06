<?php

namespace App\Repository;

use App\Entity\IgnoredComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IgnoredComment>
 *
 * @method IgnoredComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method IgnoredComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method IgnoredComment[]    findAll()
 * @method IgnoredComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IgnoredCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IgnoredComment::class);
    }

    //    /**
    //     * @return IgnoredComment[] Returns an array of IgnoredComment objects
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

    //    public function findOneBySomeField($value): ?IgnoredComment
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
