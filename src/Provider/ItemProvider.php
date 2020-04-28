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

    public function getAll(int $page = 1, int $limit = self::DEFAULT_PAGE_LIMIT): PaginationInterface
    {
        return $this->itemRepository->getAll($page, $limit);
    }

    public function getAllByAlbum(string $album, int $page = 1, int $limit = self::DEFAULT_PAGE_LIMIT): PaginationInterface
    {
        return $this->itemRepository->getAllByAlbum($album, $page, $limit);
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
