<?php

declare(strict_types=1);

namespace App\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminSettingsProvider
{
    const ITEM_FILTER_OPTIONS_ITEMS_PER_PAGE = 'itemsPerPage';
    const ITEM_FILTER_OPTIONS_ITEMS_SORT = 'itemsSort';
    const ITEM_FILTER_OPTIONS_ITEMS_SORT_DIRECTION = 'itemsSortDirection';
    const ITEM_FILTER_OPTIONS_ALBUM = 'album';

    /** @var Request|null */
    private $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getItemsPerPage(): int
    {
        if (null === $this->request) {
            return ItemProvider::DEFAULT_PAGE_LIMIT;
        }

        return (int) $this->request->cookies->get(
            self::ITEM_FILTER_OPTIONS_ITEMS_PER_PAGE,
            ItemProvider::DEFAULT_PAGE_LIMIT
        );
    }

    public function getItemsSort(): string
    {
        if (null === $this->request) {
            return ItemProvider::DEFAULT_SORT_COLUMN;
        }

        return $this->request->cookies->get(
            self::ITEM_FILTER_OPTIONS_ITEMS_SORT,
            ItemProvider::DEFAULT_SORT_COLUMN
        );
    }

    public function getItemsSortDirection(): string
    {
        if (null === $this->request) {
            return ItemProvider::DEFAULT_SORT_DIRECTION;
        }

        return $this->request->cookies->get(
            self::ITEM_FILTER_OPTIONS_ITEMS_SORT_DIRECTION,
            ItemProvider::DEFAULT_SORT_DIRECTION
        );
    }

    public function getAlbum(): string
    {
        if (null === $this->request) {
            return '';
        }

        return $this->request->cookies->get(
            self::ITEM_FILTER_OPTIONS_ALBUM,
            ''
        );
    }
}
