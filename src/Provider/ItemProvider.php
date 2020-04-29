<?php

declare(strict_types=1);

namespace App\Provider;

use App\Entity\Item;
use App\Repository\ItemRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;

class ItemProvider
{
    const DEFAULT_PAGE_LIMIT = 10;

    /** @var ItemRepository */
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getAllPaginated(int $page = 1, int $limit = self::DEFAULT_PAGE_LIMIT): PaginationInterface
    {
        return $this->itemRepository->getAllPaginated($page, $limit);
    }

    public function getAllByAlbumPaginated(string $album, int $page = 1, int $limit = self::DEFAULT_PAGE_LIMIT): PaginationInterface
    {
        return $this->itemRepository->getAllByAlbumPaginated($album, $page, $limit);
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
}
