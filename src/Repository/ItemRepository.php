<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ItemRepository extends ServiceEntityRepository
{
    const DEFAULT_SORT_ORDER = 'DESC';
    const DEFAULT_PAGE_LIMIT = 10;

    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;

        parent::__construct($registry, Item::class);
    }

    public function getAll(int $page): PaginationInterface
    {
        $query = $this->getEntityManager()->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->orderBy('i.date', self::DEFAULT_SORT_ORDER)
            ->getQuery();

        return $this->paginator->paginate(
            $query,
            $page,
            self::DEFAULT_PAGE_LIMIT
        );
    }

    public function getAllByAlbum(string $album, int $page): PaginationInterface
    {
        $query = $this->getEntityManager()->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->where('i.album = :album')
            ->setParameter('album', $album)
            ->orderBy('i.date', self::DEFAULT_SORT_ORDER)
            ->getQuery();

        return $this->paginator->paginate(
            $query,
            $page,
            self::DEFAULT_PAGE_LIMIT
        );
    }

    public function getTotalCountByAlbum(string $album): int
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select($queryBuilder->expr()->count(1))
            ->from(Item::class, 'i')
            ->where('i.album = :album')
            ->setParameter('album', $album);

        /* @noinspection PhpUnhandledExceptionInspection */
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
