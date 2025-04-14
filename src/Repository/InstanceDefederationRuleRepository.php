<?php

namespace App\Repository;

use App\Entity\InstanceDefederationRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InstanceDefederationRule>
 *
 * @method InstanceDefederationRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstanceDefederationRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstanceDefederationRule[]    findAll()
 * @method InstanceDefederationRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstanceDefederationRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstanceDefederationRule::class);
    }

    /**
     * @return array<InstanceDefederationRule>
     */
    public function findForSoftwareOrNull(?string $software): array
    {
        $builder = $this->createQueryBuilder('idr');
        if ($software === null) {
            $builder->andWhere('idr.software is null');
        } else {
            $builder
                ->andWhere('idr.software = :software or idr.software is null')
                ->setParameter('software', $software)
            ;
        }
        return $builder
            ->getQuery()
            ->execute();
    }
}
