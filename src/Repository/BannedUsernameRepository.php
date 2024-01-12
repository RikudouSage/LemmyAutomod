<?php

namespace App\Repository;

use App\Entity\BannedUsername;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BannedUsername>
 *
 * @method BannedUsername|null find($id, $lockMode = null, $lockVersion = null)
 * @method BannedUsername|null findOneBy(array $criteria, array $orderBy = null)
 * @method BannedUsername[]    findAll()
 * @method BannedUsername[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannedUsernameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannedUsername::class);
    }

//    /**
//     * @return BannedUsername[] Returns an array of BannedUsername objects
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

//    public function findOneBySomeField($value): ?BannedUsername
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
