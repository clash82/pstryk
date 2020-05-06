<?php

declare(strict_types=1);

namespace App\Provider;

use App\Repository\ImageRepository;

class FileProvider
{
    /** @var ImageRepository */
    private $fileRepository;

    public function __construct(ImageRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }
}
