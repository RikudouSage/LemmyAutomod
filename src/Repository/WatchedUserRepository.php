<?php

namespace App\Repository;

use App\Entity\WatchedUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WatchedUser>
 *
 * @method WatchedUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method WatchedUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method WatchedUser[]    findAll()
 * @method WatchedUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WatchedUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WatchedUser::class);
    }
}
