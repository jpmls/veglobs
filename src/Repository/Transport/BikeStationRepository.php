<?php

namespace App\Repository\Transport;

use App\Entity\Transport\BikeStation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BikeStationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BikeStation::class);
    }

    public function findAvailable(int $limit = 300): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.status = :status')
            ->andWhere('b.contractName = :city')
            ->setParameter('status', 'OPEN')
            ->setParameter('city', 'Paris')
            ->orderBy('b.availableBikes', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}