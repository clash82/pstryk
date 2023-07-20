<?php declare(strict_types=1);

namespace App\Value;

class ImageDetails
{
    public const ATTR_WIDTH = 'width';
    public const ATTR_HEIGHT = 'height';

    private array $cache = [];

    public function __construct(private FilePath $filePath)
    {
    }

    public function getRawWidth(): int
    {
        return $this->getDetails($this->filePath->getRawRelativePath(), self::ATTR_WIDTH);
    }

    public function getRawHeight(): int
    {
        return $this->getDetails($this->filePath->getRawRelativePath(), self::ATTR_HEIGHT);
    }

    public function getImageWidth(): int
    {
        return $this->getDetails($this->filePath->getImageRelativePath(), self::ATTR_WIDTH);
    }

    public function getImageHeight(): int
    {
        return $this->getDetails($this->filePath->getImageRelativePath(), self::ATTR_HEIGHT);
    }

    public function getThumbWidth(): int
    {
        return $this->getDetails($this->filePath->getThumbRelativePath(), self::ATTR_WIDTH);
    }

    public function getThumbHeight(): int
    {
        return $this->getDetails($this->filePath->getThumbRelativePath(), self::ATTR_HEIGHT);
    }

    private function getDetails(string $file, string $imageAttr): int
    {
        if (isset($this->cache[$file])) {
            return $this->cache[$file][$imageAttr];
        }

        $this->cache[$file] = [
            self::ATTR_WIDTH => 0,
            self::ATTR_HEIGHT => 0,
        ];

        if (file_exists($file)) {
            $imageDetails = getimagesize($file);

            if ($imageDetails !== false) {
                $this->cache[$file] = [
                    self::ATTR_WIDTH => $imageDetails[0],
                    self::ATTR_HEIGHT => $imageDetails[1],
                ];
            }
        }

        return $this->cache[$file][$imageAttr];
    }
}
