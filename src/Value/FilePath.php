<?php declare(strict_types=1);

namespace App\Value;

use App\Provider\StoragePathProvider;

class FilePath
{
    public function __construct(private StoragePathProvider $storagePathProvider, private string $filename, private string $extension)
    {
    }

    public function getImagePublicPath(): string
    {
        return $this->createPath($this->storagePathProvider->getPublicDir(StoragePathProvider::PATH_IMAGES));
    }

    public function getImageRelativePath(): string
    {
        return $this->createPath($this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_IMAGES));
    }

    public function getThumbPublicPath(): string
    {
        return $this->createPath($this->storagePathProvider->getPublicDir(StoragePathProvider::PATH_THUMBS));
    }

    public function getThumbRelativePath(): string
    {
        return $this->createPath($this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_THUMBS));
    }

    public function getRawPublicPath(): string
    {
        return $this->createPath(
            $this->storagePathProvider->getPublicDir(StoragePathProvider::PATH_RAW),
            $this->extension
        );
    }

    public function getRawRelativePath(): string
    {
        return $this->createPath(
            $this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_RAW),
            $this->extension
        );
    }

    private function createPath(string $path, string $extension = 'jpg'): string
    {
        return sprintf('%s/%s.%s', $path, $this->filename, $extension);
    }
}
