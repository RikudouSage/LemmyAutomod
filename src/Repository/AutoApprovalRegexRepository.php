<?php

namespace App\Repository;

use App\Entity\AutoApprovalRegex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AutoApprovalRegex>
 *
 * @method AutoApprovalRegex|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoApprovalRegex|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoApprovalRegex[]    findAll()
 * @method AutoApprovalRegex[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoApprovalRegexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AutoApprovalRegex::class);
    }

//    /**
//     * @return AutoApprovalRegex[] Returns an array of AutoApprovalRegex objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AutoApprovalRegex
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
