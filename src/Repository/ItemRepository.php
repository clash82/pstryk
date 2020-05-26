<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Item;
use App\Exception\RecordNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ItemRepository extends ServiceEntityRepository
{
    /** @var PaginatorInterface */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;

        parent::__construct($registry, Item::class);
    }

    public function getAllPaginated(
        string $album,
        int $page,
        int $itemsPerPage,
        string $itemsSort,
        string $itemsSortDirection
    ): PaginationInterface {
        $query = $this
            ->createQueryBuilder('i')
            ->orderBy(sprintf('i.%s', $itemsSort), $itemsSortDirection);

        if ('' !== $album) {
            $query
                ->where('i.album = :album')
                ->setParameter('album', $album);
        }

        return $this->paginator->paginate(
            $query->getQuery(),
            $page,
            $itemsPerPage
        );
    }

    /**
     * @return Item[]
     */
    public function getAllByAlbum(string $album): array
    {
        return $this
            ->createQueryBuilder('i')
            ->where('i.album = :album')
            ->setParameter('album', $album)
            ->orderBy('i.date', 'DESC')
            ->getQuery()->getResult();
    }

    public function getTotalCountByAlbum(string $album): int
    {
        $queryBuilder = $this->createQueryBuilder('i');

        $queryBuilder
            ->select($queryBuilder->expr()->count(1))
            ->where('i.album = :album')
            ->setParameter('album', $album);

        /* @noinspection PhpUnhandledExceptionInspection */
        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function getById(int $itemId): Item
    {
        $item = $this->getEntityManager()->getRepository(Item::class)
            ->find($itemId);

        if (!$item) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new RecordNotFoundException(sprintf('No item found for id [%d]', $itemId));
        }

        return $item;
    }

    public function getBySlug(string $albumSlug, string $itemSlug): array
    {
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $nextSlug = $this->createQueryBuilder('ns')
            ->select($expr->min('ns.slug'))
            ->where($expr->gt('ns.date', 'i.date'))
            ->andWhere('ns.album = :album');

        $nextTitle = $this->createQueryBuilder('nt')
            ->select('nt.title')
            ->where($expr->gt('nt.date', 'i.date'))
            ->andWhere('nt.album = :album');

        $previousSlug = $this->createQueryBuilder('ps')
            ->select($expr->max('ps.slug'))
            ->where($expr->lt('ps.date', 'i.date'))
            ->andWhere('ps.album = :album');

        $previousTitle = $this->createQueryBuilder('pt')
            ->select('pt.title')
            ->where($expr->lt('pt.date', 'i.date'))
            ->andWhere('pt.album = :album');

        try {
            /* @noinspection PhpUnhandledExceptionInspection */
            $item = $this->createQueryBuilder('i')
                ->select('i as current')
                ->addSelect(sprintf('(%s) as prev_slug', $previousSlug->getDQL()))
                ->addSelect(sprintf('(%s) as prev_title', $previousTitle->getDQL()))
                ->addSelect(sprintf('(%s) as next_slug', $nextSlug->getDQL()))
                ->addSelect(sprintf('(%s) as next_title', $nextTitle->getDQL()))
                ->where('i.slug = :slug')
                ->andWhere('i.album = :album')
                ->setParameter('slug', $itemSlug)
                ->setParameter('album', $albumSlug)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new RecordNotFoundException(sprintf('No item found for slug [%s:%s]', $albumSlug, $itemSlug));
        }

        return $item;
    }

    public function deleteById(int $itemId): void
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $item = $this->getById($itemId);

        /* @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->remove($item);
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->flush();
    }

    public function update(Item $item): void
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->persist($item);
        /* @noinspection PhpUnhandledExceptionInspection */
        $this->getEntityManager()->flush();
    }
}
