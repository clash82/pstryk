<?php

declare(strict_types=1);

namespace App\Provider;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;

class ItemProvider
{
    const DEFAULT_PAGE_LIMIT = 10;
    const DEFAULT_SORT_COLUMN = 'date';
    const DEFAULT_SORT_DIRECTION = 'desc';

    /** @var ItemRepository */
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getAllPaginated(
        string $album,
        int $page = 1,
        int $itemsPerPage = self::DEFAULT_PAGE_LIMIT,
        string $itemsSort = self::DEFAULT_SORT_COLUMN,
        string $itemsSortDirection = self::DEFAULT_SORT_DIRECTION,
        bool $activeOnly = true
    ): PaginationInterface {
        return $this->itemRepository->getAllPaginated(
            $album,
            $page,
            $itemsPerPage,
            $itemsSort,
            $itemsSortDirection,
            $activeOnly
        );
    }

    /**
     * @return Item[]
     */
    public function getAllByAlbum(string $album): array
    {
        return $this->itemRepository->getAllByAlbum($album);
    }

    public function getTotalCountByAlbum(string $album): int
    {
        return $this->itemRepository->getTotalCountByAlbum($album);
    }

    public function getById(int $itemId): Item
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->itemRepository->getById($itemId);
    }

    public function getBySlug(string $albumSlug, string $itemSlug): Item
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->itemRepository->getBySlug($albumSlug, $itemSlug);
    }

    public function getNext(Item $item): ?Item
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->itemRepository->getNext($item);
    }

    public function getPrevious(Item $item): ?Item
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->itemRepository->getPrevious($item);
    }
}
