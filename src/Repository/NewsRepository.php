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

    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, News>, total: int}
     */
    public function search(array $filters, int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('n')
            ->orderBy('n.publishedAt', 'DESC');

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

        if (!empty($filters['source'])) {
            $qb->andWhere('n.source = :source')
                ->setParameter('source', $filters['source']);
        }

        if (!empty($filters['q'])) {
            $qb->andWhere('n.title LIKE :q OR n.content LIKE :q')
                ->setParameter('q', '%' . $filters['q'] . '%');
        }

        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(n.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => $total,
        ];
    }
}