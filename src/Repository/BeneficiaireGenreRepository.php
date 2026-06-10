<?php

namespace App\Repository;

use App\Entity\BeneficiaireGenre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BeneficiaireGenre>
 */
class BeneficiaireGenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BeneficiaireGenre::class);
    }
}
