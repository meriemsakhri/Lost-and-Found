<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * Items for the dashboard, with optional text search + category filter.
     *
     * @return Item[]
     */
    public function searchForDashboard(?string $q, ?string $category): array
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.status = :active')
            ->setParameter('active', 'active')
            ->orderBy('i.date', 'DESC');

        // Optional text search in title/description/location
        if ($q) {
            $q = mb_strtolower($q);

            $qb
                ->andWhere(
                    'LOWER(i.title) LIKE :q OR LOWER(i.description) LIKE :q OR LOWER(i.location) LIKE :q'
                )
                ->setParameter('q', '%' . $q . '%');
        }

        // Optional exact category filter
        if ($category) {
            $qb
                ->andWhere('i.category = :cat')
                ->setParameter('cat', $category);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all distinct non-empty categories, sorted alphabetically.
     *
     * @return string[]
     */
    public function findDistinctCategories(): array
    {
        $rows = $this->createQueryBuilder('i')
            ->select('DISTINCT i.category AS category')
            ->where('i.category IS NOT NULL')
            ->andWhere('i.category <> :empty')
            ->setParameter('empty', '')
            ->orderBy('i.category', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'category');
    }
}
