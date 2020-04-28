<?php

declare(strict_types=1);

namespace App\Image;

use App\Entity\File;
use App\Exception\AlbumNotSpecifiedException;
use App\Provider\TagsProvider;
use App\Value\Album;
use iBudasov\Iptc\Domain\Tag;
use iBudasov\Iptc\Manager;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImageConverter
{
    /** @var Album */
    private $album = null;

    /** @var TagsProvider */
    private $tagsProvider;

    public function __construct(TagsProvider $tagsProvider)
    {
        $this->tagsProvider = $tagsProvider;
    }

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

        // saving tags
        $manager = Manager::create();
        $manager->loadFile($destinationFile);

        /* @noinspection PhpUnhandledExceptionInspection */
        $tags = $this->tagsProvider->get($this->album);
        $manager->addTag(new Tag(Tag::AUTHOR, [$tags->getAuthor()]));
        $manager->addTag(new Tag(Tag::COPYRIGHT_STRING, [$tags->getCopyright()]));
        $manager->addTag(new Tag(Tag::DESCRIPTION, [$tags->getDescription()]));

        try {
            $manager->write();
        } catch (\Exception $e) {
            // should be fine, but in any case we'll just skip
        }
    }
}
