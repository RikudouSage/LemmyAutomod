<?php

namespace App\Repository;

use App\Entity\ReportRegex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReportRegex>
 *
 * @method ReportRegex|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportRegex|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportRegex[]    findAll()
 * @method ReportRegex[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRegexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportRegex::class);
    }

//    /**
//     * @return ReportRegex[] Returns an array of ReportRegex objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReportRegex
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
