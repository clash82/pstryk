<?php declare(strict_types=1);

namespace App\Provider;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;

class ItemProvider
{
    final public const int DEFAULT_PAGE_LIMIT = 10;
    final public const string DEFAULT_SORT_COLUMN = 'date';
    final public const string DEFAULT_SORT_DIRECTION = 'desc';

    public function __construct(private readonly ItemRepository $itemRepository)
    {
    }

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
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

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function getBySlug(string $albumSlug, string $itemSlug, bool $activeOnly = true): Item
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->itemRepository->getBySlug($albumSlug, $itemSlug, $activeOnly);
    }

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function getNext(Item $item, bool $activeOnly = true): ?Item
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->itemRepository->getNext($item, $activeOnly);
    }

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function getPrevious(Item $item, bool $activeOnly = true): ?Item
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->itemRepository->getPrevious($item, $activeOnly);
    }
}
