<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Item;
use App\Exception\RecordNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        $query = $this->getEntityManager()->getRepository(Item::class)
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
        return $this->getEntityManager()->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->where('i.album = :album')
            ->setParameter('album', $album)
            ->orderBy('i.date', 'DESC')
            ->getQuery()->getResult();
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
