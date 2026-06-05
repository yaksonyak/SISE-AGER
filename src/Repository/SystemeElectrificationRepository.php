<?php

namespace App\Repository;

use App\Entity\SystemeElectrification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SystemeElectrification>
 */
class SystemeElectrificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemeElectrification::class);
    }
}
