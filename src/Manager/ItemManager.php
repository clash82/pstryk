<?php declare(strict_types=1);

namespace App\Manager;

use App\Entity\Item;
use App\Repository\ItemRepository;

class ItemManager
{
    public function __construct(private ItemRepository $itemRepository)
    {
    }

    public function deleteById(int $itemId): void
    {
        $this->itemRepository->deleteById($itemId);
    }

    public function update(Item $item): void
    {
        $this->itemRepository->update($item);
    }
}
