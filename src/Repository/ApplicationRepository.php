<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Application> */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Application::class); }

    /** @return list<Application> */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.published = true')
            ->orderBy('a.position', 'ASC')->addOrderBy('a.name', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return list<Application> */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['position' => 'ASC', 'name' => 'ASC']);
    }

    public function nextPosition(): int
    {
        return (int) $this->createQueryBuilder('a')->select('COALESCE(MAX(a.position), 0)')->getQuery()->getSingleScalarResult() + 1;
    }
}
