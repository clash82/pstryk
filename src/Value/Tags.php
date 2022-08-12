<?php declare(strict_types=1);

namespace App\Value;

use App\Exception\ArrayKeyNotExistsException;

class Tags
{
    private string $author;

    private string $copyright;

    private string $description;

    public function __construct(array $settings = [])
    {
        if (!isset($settings['tags']['author'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('tags:author');
        }
        $this->author = $settings['tags']['author'];

        if (!isset($settings['tags']['copyright'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('tags:copyright');
        }
        $this->copyright = $settings['tags']['copyright'];

        if (!isset($settings['tags']['description'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('tags:description');
        }
        $this->description = $settings['tags']['description'];
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
