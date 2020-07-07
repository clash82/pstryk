<?php

declare(strict_types=1);

namespace App\Value;

use App\Exception\FileNotExistsException;

class ImageDetails
{
    const ATTR_WIDTH = 'width';
    const ATTR_HEIGHT = 'height';

    /** @var FilePath */
    private $filePath;

    /** @var array */
    private $cache = [];

    public function __construct(FilePath $filePath)
    {
        $this->filePath = $filePath;
    }

    public function getRawWidth(): int
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getDetails($this->filePath->getRawRelativePath(), self::ATTR_WIDTH);
    }

    public function getRawHeight(): int
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getDetails($this->filePath->getRawRelativePath(), self::ATTR_HEIGHT);
    }

    public function getImageWidth(): int
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getDetails($this->filePath->getImageRelativePath(), self::ATTR_WIDTH);
    }

    public function getImageHeight(): int
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getDetails($this->filePath->getImageRelativePath(), self::ATTR_HEIGHT);
    }

    public function getThumbWidth(): int
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getDetails($this->filePath->getThumbRelativePath(), self::ATTR_WIDTH);
    }

    public function getThumbHeight(): int
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getDetails($this->filePath->getThumbRelativePath(), self::ATTR_HEIGHT);
    }

    private function getDetails(string $file, string $imageAttr): int
    {
        if (!isset($this->cache[$file])) {
            if (!file_exists($file)) {
                /* @noinspection PhpUnhandledExceptionInspection */
                throw new FileNotExistsException($file);
            }

            list($width, $height) = getimagesize($file);

            $this->cache[$file] = [
                self::ATTR_WIDTH => $width,
                self::ATTR_HEIGHT => $height,
            ];
        }

        return $this->cache[$file][$imageAttr];
    }
}
