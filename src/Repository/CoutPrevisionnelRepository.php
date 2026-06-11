<?php

namespace App\Repository;

use App\Entity\CoutPrevisionnel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoutPrevisionnel>
 */
class CoutPrevisionnelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoutPrevisionnel::class);
    }
}
