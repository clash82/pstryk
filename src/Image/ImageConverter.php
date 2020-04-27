<?php

declare(strict_types=1);

namespace App\Image;

use App\Entity\File;
use App\Exception\AlbumNotSpecifiedException;
use App\Value\Album;

class ImageConverter
{
    /** @var Album */
    private $album = null;

    public function setAlbum(Album $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function convert(File $file): bool
    {
        if ($this->album === null) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new AlbumNotSpecifiedException();
        }

        // todo: convert files based on the settings given in $album object
        copy($file->getRawRelativePath(), $file->getImagesRelativePath());
        copy($file->getRawRelativePath(), $file->getThumbsRelativePath());

        return true;
    }
}
