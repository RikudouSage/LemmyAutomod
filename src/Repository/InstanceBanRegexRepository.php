<?php

namespace App\Repository;

use App\Entity\InstanceBanRegex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InstanceBanRegex>
 *
 * @method InstanceBanRegex|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstanceBanRegex|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstanceBanRegex[]    findAll()
 * @method InstanceBanRegex[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstanceBanRegexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstanceBanRegex::class);
    }

//    /**
//     * @return InstanceBanRegex[] Returns an array of InstanceBanRegex objects
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

//    public function findOneBySomeField($value): ?InstanceBanRegex
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
