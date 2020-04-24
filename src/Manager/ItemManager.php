<?php

declare(strict_types=1);

namespace App\Manager;

use App\Repository\ItemRepository;

class ItemManager
{
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function deleteById(int $itemId): void
    {
        $this->itemRepository->deleteById($itemId);
    }
}
