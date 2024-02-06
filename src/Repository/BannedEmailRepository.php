<?php

namespace App\Repository;

use App\Entity\BannedEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BannedEmail>
 *
 * @method BannedEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method BannedEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method BannedEmail[]    findAll()
 * @method BannedEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannedEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannedEmail::class);
    }

//    /**
//     * @return BannedEmail[] Returns an array of BannedEmail objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BannedEmail
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
