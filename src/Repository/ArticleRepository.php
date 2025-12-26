<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Search articles by title (case-insensitive, partial match).
     *
     * @param string|null $query
     * @return Article[]
     */
    public function searchByTitle(?string $query): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC');

        if ($query !== null && trim($query) !== '') {
            $qb->andWhere('LOWER(a.title) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower(trim($query)) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Search articles by title and limit results (useful for autocomplete).
     *
     * @param string|null $query
     * @param int $limit
     * @return Article[]
     */
    public function searchByTitleLimited(?string $query, int $limit = 7): array
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($query !== null && trim($query) !== '') {
            $qb->andWhere('LOWER(a.title) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower(trim($query)) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find articles authored by a specific user, ordered by newest first.
     *
     * @return Article[]
     */
    public function findByAuthor($user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.Author = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
