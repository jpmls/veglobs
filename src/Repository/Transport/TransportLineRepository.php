<?php

namespace App\Repository\Transport;

use App\Entity\Transport\TransportLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransportLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransportLine::class);
    }

    /**
     * @return TransportLine[]
     */
    public function findPaginated(?string $mode, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('l')
            ->orderBy('l.transportMode', 'ASC')
            ->addOrderBy('l.shortName', 'ASC')
            ->addOrderBy('l.name', 'ASC');

        if ($mode !== null) {
            $qb->andWhere('LOWER(l.transportMode) = LOWER(:mode)')
               ->setParameter('mode', $mode);
        }

        return $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countFiltered(?string $mode): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)');

        if ($mode !== null) {
            $qb->andWhere('LOWER(l.transportMode) = LOWER(:mode)')
               ->setParameter('mode', $mode);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
