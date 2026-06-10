<?php

namespace App\Repository;

use App\Entity\DonneeGeospatialeLocalite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DonneeGeospatialeLocalite>
 */
class DonneeGeospatialeLocaliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DonneeGeospatialeLocalite::class);
    }
}
