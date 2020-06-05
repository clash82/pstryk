<?php

declare(strict_types=1);

namespace App\Provider;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StoragePathProvider
{
    const PATH_RAW = 0;
    const PATH_IMAGES = 1;
    const PATH_THUMBS = 2;

    const RELATIVE_PATH_PATTERN = '%s/../../public_html/%s';

    /** @var ParameterBagInterface */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getPublicDir(int $path): string
    {
        if (self::PATH_IMAGES === $path) {
            return sprintf('/%s', $this->parameterBag->get('app')['storage_images_path']);
        }

        if (self::PATH_THUMBS === $path) {
            return sprintf('/%s', $this->parameterBag->get('app')['storage_thumbs_path']);
        }

        return sprintf('/%s', $this->parameterBag->get('app')['storage_raw_path']);
    }

    public function getRelativeDir(int $path): string
    {
        if (self::PATH_IMAGES === $path) {
            return sprintf(self::RELATIVE_PATH_PATTERN, __DIR__, $this->parameterBag->get('app')['storage_images_path']);
        }

        if (self::PATH_THUMBS === $path) {
            return sprintf(self::RELATIVE_PATH_PATTERN, __DIR__, $this->parameterBag->get('app')['storage_thumbs_path']);
        }

        return sprintf(self::RELATIVE_PATH_PATTERN, __DIR__, $this->parameterBag->get('app')['storage_raw_path']);
    }
}
