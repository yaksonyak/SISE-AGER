<?php

namespace App\Repository;

use App\Entity\SiteEnergetique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteEnergetique>
 */
class SiteEnergetiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteEnergetique::class);
    }
}
