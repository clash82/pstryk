<?php

declare(strict_types=1);

namespace App\Provider;

use App\Repository\FileRepository;

class FileProvider
{
    /** @var FileRepository */
    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }
}
