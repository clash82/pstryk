<?php

declare(strict_types=1);

namespace App\Image;

use App\Entity\File;
use App\Exception\AlbumNotSpecifiedException;
use App\Value\Album;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageConverter
{
    /** @var Album */
    private $album = null;

    public function setAlbum(Album $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function convert(File $file): void
    {
        if (null === $this->album) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new AlbumNotSpecifiedException();
        }

        // normal
        $this->resize(
            $file->getRawRelativePath(),
            $file->getImagesRelativePath(),
            $this->album->getImageHorizontalMaxWidth(),
            $this->album->getImageVerticalMaxHeight()
        );

        // thumb
        $this->resize(
            $file->getRawRelativePath(),
            $file->getThumbsRelativePath(),
            $this->album->getThumbHorizontalMaxWidth(),
            $this->album->getThumbVerticalMaxHeight()
        );
    }

    private function resize(
        string $sourceFile,
        string $destinationFile,
        int $horizontalMaxWidth,
        int $verticalMaxHeight
    ): void {
        $imagine = new Imagine();

        $image = $imagine->open($sourceFile);
        $size = $image->getSize();

        if (($horizontalMaxWidth > 0 && $size->getWidth() > $horizontalMaxWidth)
            || ($verticalMaxHeight > 0 && $size->getHeight() > $verticalMaxHeight)) {
            $newSize = new Box($size->getWidth(), $size->getHeight());

            // horizontal
            if ($size->getWidth() > $size->getHeight()) {
                $newSize = new Box(
                    (int) ($size->getWidth() * ($horizontalMaxWidth / $size->getWidth())),
                    (int) ($size->getHeight() * ($horizontalMaxWidth / $size->getWidth()))
                );
            }

            // vertical
            if ($size->getWidth() < $size->getHeight()) {
                $newSize = new Box(
                    (int) ($size->getWidth() * ($verticalMaxHeight / $size->getHeight())),
                    (int) ($size->getHeight() * ($verticalMaxHeight / $size->getHeight()))
                );
            }

            $image->resize($newSize);
        }

        $image->save($destinationFile, [
            'jpeg_quality' => 90,
        ]);
    }
}
