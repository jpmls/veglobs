<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function save(News $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(News $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function search(array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.author', 'a')
            ->addSelect('a');

        if (!empty($filters['network'])) {
            $qb->andWhere('n.network = :network')
               ->setParameter('network', $filters['network']);
        }

        if (!empty($filters['line'])) {
            $qb->andWhere('n.line = :line')
               ->setParameter('line', $filters['line']);
        }

        if (!empty($filters['type'])) {
            $qb->andWhere('n.type = :type')
               ->setParameter('type', $filters['type']);
        }

        $qb->orderBy('n.publishedAt', 'DESC');

        // Total avant pagination
        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(n.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return [
            'items' => $qb->getQuery()->getResult(),
            'total' => $total,
        ];
    }
}