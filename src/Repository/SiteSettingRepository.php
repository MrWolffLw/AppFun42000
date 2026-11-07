<?php

namespace App\Repository;

use App\Entity\SiteSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SiteSetting> */
class SiteSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, SiteSetting::class); }

    /** Renvoie les réglages ; la ligne est créée à la première modification depuis l'administration. */
    public function get(): SiteSetting
    {
        return $this->findOneBy([], ['id' => 'ASC']) ?? new SiteSetting();
    }
}
