<?php

declare(strict_types=1);

namespace App\Value;

use App\Exception\ArrayKeyNotExistsException;

class Tags
{
    /** @var string */
    private $author;

    /** @var string */
    private $copyright;

    /** @var string */
    private $description;

    public function __construct(array $tags = [])
    {
        if (!isset($tags['author'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('author');
        }
        $this->author = $tags['author'];

        if (!isset($tags['copyright'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('copyright');
        }
        $this->copyright = $tags['copyright'];

        if (!isset($tags['description'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('description');
        }
        $this->description = $tags['description'];
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getCopyright(): string
    {
        return $this->copyright;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
