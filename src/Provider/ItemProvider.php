<?php

declare(strict_types=1);

namespace App\Provider;

use App\Repository\ItemRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;

class ItemProvider
{
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getAll(int $page = 1): PaginationInterface
    {
        return $this->itemRepository->getAll($page);
    }

    public function getAllByAlbum(string $album, int $page = 1): PaginationInterface
    {
        return $this->itemRepository->getAllByAlbum($album, $page);
    }

    public function getTotalCountByAlbum(string $album): int
    {
        return $this->itemRepository->getTotalCountByAlbum($album);
    }
}
