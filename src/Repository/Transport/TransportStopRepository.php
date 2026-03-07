<?php

namespace App\Repository\Transport;

use App\Entity\Transport\TransportStop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransportStopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransportStop::class);
    }

    /**
     * @return TransportStop[]
     */
    public function searchByName(string $query, int $limit = 20): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('LOWER(s.name) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($query) . '%')
            ->orderBy('s.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}