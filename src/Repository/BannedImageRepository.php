<?php

namespace App\Repository;

use App\Entity\BannedImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BannedImage>
 *
 * @method BannedImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method BannedImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method BannedImage[]    findAll()
 * @method BannedImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannedImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannedImage::class);
    }

//    /**
//     * @return BannedImage[] Returns an array of BannedImage objects
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

//    public function findOneBySomeField($value): ?BannedImage
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
