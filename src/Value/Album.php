<?php

declare(strict_types=1);

namespace App\Value;

use App\Exception\ArrayKeyNotExistsException;

class Album
{
    /** @var string */
    private $slug;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var int */
    private $paginationLimit;

    /** @var int */
    private $feedLimit;

    /** @var string */
    private $feedUrl;

    /** @var int */
    private $imageHorizontalMaxWidth;

    /** @var int */
    private $imageVerticalMaxHeight;

    /** @var int */
    private $thumbHorizontalMaxWidth;

    /** @var int */
    private $thumbVerticalMaxHeight;

    public function __construct(string $slug, array $album = [])
    {
        $this->slug = $slug;

        if (!isset($album['title'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('title');
        }
        $this->title = $album['title'];

        if (!isset($album['description'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('description');
        }
        $this->description = $album['description'];

        if (!isset($album['pagination_limit'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('pagination_limit');
        }
        $this->paginationLimit = (int) $album['pagination_limit'];

        if (!isset($album['feed_limit'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('feed_limit');
        }
        $this->feedLimit = (int) $album['feed_limit'];

        if (!isset($album['feed_url'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('feed_url');
        }
        $this->feedUrl = $album['feed_url'];

        if (!isset($album['image_horizontal_max_width'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('image_horizontal_max_width');
        }
        $this->imageHorizontalMaxWidth = (int) $album['image_horizontal_max_width'];

        if (!isset($album['image_vertical_max_height'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('image_vertical_max_height');
        }
        $this->imageVerticalMaxHeight = (int) $album['image_vertical_max_height'];

        if (!isset($album['thumb_horizontal_max_width'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('thumb_horizontal_max_width');
        }
        $this->thumbHorizontalMaxWidth = (int) $album['thumb_horizontal_max_width'];

        if (!isset($album['thumb_vertical_max_height'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('thumb_vertical_max_height');
        }
        $this->thumbVerticalMaxHeight = (int) $album['thumb_vertical_max_height'];
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPaginationLimit(): int
    {
        return $this->paginationLimit;
    }

    public function getFeedLimit(): int
    {
        return $this->feedLimit;
    }

    public function getFeedUrl(): string
    {
        return $this->feedUrl;
    }

    public function getImageHorizontalMaxWidth(): int
    {
        return $this->imageHorizontalMaxWidth;
    }

    public function getImageVerticalMaxHeight(): int
    {
        return $this->imageVerticalMaxHeight;
    }

    public function getThumbHorizontalMaxWidth(): int
    {
        return $this->thumbHorizontalMaxWidth;
    }

    public function getThumbVerticalMaxHeight(): int
    {
        return $this->thumbVerticalMaxHeight;
    }
}
