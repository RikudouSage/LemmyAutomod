<?php

namespace App\Repository;

use App\Entity\TrustedUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrustedUser>
 *
 * @method TrustedUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrustedUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrustedUser[]    findAll()
 * @method TrustedUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrustedUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrustedUser::class);
    }
}
