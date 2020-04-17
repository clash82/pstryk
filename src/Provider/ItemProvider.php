<?php

declare(strict_types=1);

namespace App\Provider;

use App\Repository\ItemRepository;

class ItemProvider
{
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getAllByAlbum(string $album, int $limit = null, int $offset = null): array
    {
        return $this->itemRepository->getAllByAlbum($album, $limit ,$offset);
    }

    public function getTotalCountByAlbum(string $album): int
    {
        return $this->itemRepository->getTotalCountByAlbum($album);
    }
}
