<?php

namespace App\Repository;

use App\Entity\IgnoredUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IgnoredUser>
 *
 * @method IgnoredUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method IgnoredUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method IgnoredUser[]    findAll()
 * @method IgnoredUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IgnoredUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IgnoredUser::class);
    }

//    /**
//     * @return IgnoredUser[] Returns an array of IgnoredUser objects
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

//    public function findOneBySomeField($value): ?IgnoredUser
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
