<?php declare(strict_types=1);

namespace App\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminSettingsProvider
{
    final public const ITEM_FILTER_OPTIONS_ITEMS_PER_PAGE = 'itemsPerPage';
    final public const ITEM_FILTER_OPTIONS_ITEMS_SORT = 'itemsSort';
    final public const ITEM_FILTER_OPTIONS_ITEMS_SORT_DIRECTION = 'itemsSortDirection';
    final public const ITEM_FILTER_OPTIONS_ALBUM = 'album';

    private readonly ?Request $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getItemsPerPage(): int
    {
        if (!$this->request instanceof Request) {
            return ItemProvider::DEFAULT_PAGE_LIMIT;
        }

        return (int) $this->request->cookies->get(
            self::ITEM_FILTER_OPTIONS_ITEMS_PER_PAGE,
            (string) ItemProvider::DEFAULT_PAGE_LIMIT
        );
    }

    public function getItemsSort(): string
    {
        if (!$this->request instanceof Request) {
            return ItemProvider::DEFAULT_SORT_COLUMN;
        }

        return (string) $this->request->cookies->get(
            self::ITEM_FILTER_OPTIONS_ITEMS_SORT,
            ItemProvider::DEFAULT_SORT_COLUMN
        );
    }

    public function getItemsSortDirection(): string
    {
        if (!$this->request instanceof Request) {
            return ItemProvider::DEFAULT_SORT_DIRECTION;
        }

        return (string) $this->request->cookies->get(
            self::ITEM_FILTER_OPTIONS_ITEMS_SORT_DIRECTION,
            ItemProvider::DEFAULT_SORT_DIRECTION
        );
    }

    public function getAlbum(): string
    {
        if (!$this->request instanceof Request) {
            return '';
        }

        return (string) $this->request->cookies->get(
            self::ITEM_FILTER_OPTIONS_ALBUM,
            ''
        );
    }
}
