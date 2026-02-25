<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    /**
     * @return array{items: Comment[], total: int}
     */
    public function findByNewsPaginated(int $newsId, int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = min(max(1, $limit), 50);
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.news = :newsId')
            ->setParameter('newsId', $newsId)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        $total = (int) $this->createQueryBuilder('c2')
            ->select('COUNT(c2.id)')
            ->andWhere('c2.news = :newsId')
            ->setParameter('newsId', $newsId)
            ->getQuery()
            ->getSingleScalarResult();

        return ['items' => $items, 'total' => $total];
    }
}