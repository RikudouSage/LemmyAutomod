<?php

namespace App\Repository;

use App\Entity\ComplexRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ComplexRule>
 *
 * @method ComplexRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComplexRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComplexRule[]    findAll()
 * @method ComplexRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComplexRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComplexRule::class);
    }

//    /**
//     * @return ComplexRule[] Returns an array of ComplexRule objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ComplexRule
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
