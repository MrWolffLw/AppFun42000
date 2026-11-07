<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Media> */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Media::class); }

    public function nextPosition(Application $application): int
    {
        return (int) $this->createQueryBuilder('m')->select('COALESCE(MAX(m.position), 0)')
            ->andWhere('m.application = :a')->setParameter('a', $application)
            ->getQuery()->getSingleScalarResult() + 1;
    }
}
