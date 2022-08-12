<?php declare(strict_types=1);

namespace App\Provider;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StoragePathProvider
{
    public const PATH_RAW = 0;
    public const PATH_IMAGES = 1;
    public const PATH_THUMBS = 2;

    public const RELATIVE_PATH_PATTERN = '%s/../../public_html/%s';

    private string $storageImagesPath;

    private string $storageThumbsPath;

    private string $storageRawPath;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        /** @var array $app */
        $app = $parameterBag->get('app');

        $this->storageImagesPath = $app['storage_images_path'];
        $this->storageThumbsPath = $app['storage_thumbs_path'];
        $this->storageRawPath = $app['storage_raw_path'];
    }

    public function getPublicDir(int $path): string
    {
        if (self::PATH_IMAGES === $path) {
            return sprintf('/%s', $this->storageImagesPath);
        }

        if (self::PATH_THUMBS === $path) {
            return sprintf('/%s', $this->storageThumbsPath);
        }

        return sprintf('/%s', $this->storageRawPath);
    }

    public function getRelativeDir(int $path): string
    {
        if (self::PATH_IMAGES === $path) {
            return sprintf(self::RELATIVE_PATH_PATTERN, __DIR__, $this->storageImagesPath);
        }

        if (self::PATH_THUMBS === $path) {
            return sprintf(self::RELATIVE_PATH_PATTERN, __DIR__, $this->storageThumbsPath);
        }

        return sprintf(self::RELATIVE_PATH_PATTERN, __DIR__, $this->storageRawPath);
    }
}
