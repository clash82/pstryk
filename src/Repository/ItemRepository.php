<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function getAllByAlbum(string $album, int $limit = null, int $offset = null): array
    {
        return $this->getEntityManager()->getRepository(Item::class)->findBy([
            'album' => $album,
        ], ['date' => 'DESC'], $limit, $offset);
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
