<?php

declare(strict_types=1);

namespace App\Image;

use App\Entity\Image as ImageEntity;
use App\Exception\AlbumNotSpecifiedException;
use App\Exception\FileNotExistsException;
use App\Provider\TagsSettingsProvider;
use App\Provider\WatermarkSettingsProvider;
use App\Value\Album;
use App\Value\Enum\WatermarkPosition;
use App\Value\Watermark;
use iBudasov\Iptc\Domain\Binary;
use iBudasov\Iptc\Domain\Tag;
use iBudasov\Iptc\Infrastructure\StandardPhpFileSystem;
use iBudasov\Iptc\Infrastructure\StandardPhpImage;
use iBudasov\Iptc\Manager;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use ReflectionProperty;

class ImageConverter
{
    use UnsharpMaskTrait;

    const JPEG_QUALITY = 93;

    /** @var Album */
    private $album = null;

    /** @var bool */
    private $applyUnsharpMask = true;

    /** @var TagsSettingsProvider */
    private $tagsProvider;

    /** @var WatermarkSettingsProvider */
    private $watermarkProvider;

    /** @var ImageInterface */
    private $watermark = null;

    public function __construct(TagsSettingsProvider $tagsProvider, WatermarkSettingsProvider $watermarkProvider)
    {
        $this->tagsProvider = $tagsProvider;
        $this->watermarkProvider = $watermarkProvider;
    }

    public function setAlbum(Album $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function setApplyUnsharpMask(bool $applyUnsharpMask): self
    {
        $this->applyUnsharpMask = $applyUnsharpMask;

        return $this;
    }

    public function convert(ImageEntity $image): void
    {
        if (null === $this->album) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new AlbumNotSpecifiedException();
        }

        // normal
        $this->resize(
            $image->getRawRelativePath(),
            $image->getImagesRelativePath(),
            $this->album->getImageHorizontalMaxWidth(),
            $this->album->getImageVerticalMaxHeight()
        );

        // thumb
        $this->resize(
            $image->getRawRelativePath(),
            $image->getThumbsRelativePath(),
            $this->album->getThumbHorizontalMaxWidth(),
            $this->album->getThumbVerticalMaxHeight()
        );
    }

    private function addWatermark(
        Imagine $imagine,
        ImageInterface $image,
        Watermark $watermarkSettings
    ): ImageInterface {
        if (null === $this->watermark) {
            $watermarkFilename = sprintf(
                '%s/assets/%s/images/%s',
                getcwd(),
                $this->album->getSlug(),
                $watermarkSettings->getFile()
            );

            if (!file_exists($watermarkFilename)) {
                /* @noinspection PhpUnhandledExceptionInspection */
                throw new FileNotExistsException($watermarkFilename);
            }

            $watermark = $imagine->open($watermarkFilename);
            $watermarkSize = $watermark->getSize();

            if ($watermarkSettings->getWidth() > 0 && $watermarkSize->getWidth() > $watermarkSettings->getWidth()
                || $watermarkSettings->getHeight() > 0 && $watermarkSize->getHeight() > $watermarkSettings->getHeight()) {
                $watermarkNewSize = new Box($watermarkSettings->getWidth(), $watermarkSettings->getHeight());
                $watermark->resize($watermarkNewSize);
            }

            $this->watermark = $watermark;
        }

        $imageSize = $image->getSize();
        $watermarkSize = $this->watermark->getSize();

        $position = new Point(
            $imageSize->getWidth() - $watermarkSize->getWidth() - $watermarkSettings->getHorizontalMargin(),
            $imageSize->getHeight() - $watermarkSize->getHeight() - $watermarkSettings->getVerticalMargin()
        );

        if (WatermarkPosition::POSITION_TOP_LEFT === $watermarkSettings->getPosition()) {
            $position = new Point(
                $watermarkSettings->getHorizontalMargin(),
                $watermarkSettings->getVerticalMargin()
            );
        }

        if (WatermarkPosition::POSITION_TOP_RIGHT === $watermarkSettings->getPosition()) {
            $position = new Point(
                $imageSize->getWidth() - $watermarkSize->getWidth() - $watermarkSettings->getHorizontalMargin(),
                $watermarkSettings->getVerticalMargin()
            );
        }

        if (WatermarkPosition::POSITION_BOTTOM_LEFT === $watermarkSettings->getPosition()) {
            $position = new Point(
                $watermarkSettings->getHorizontalMargin(),
                $imageSize->getHeight() - $watermarkSize->getHeight() - $watermarkSettings->getVerticalMargin()
            );
        }

        $image->paste($this->watermark, $position, $watermarkSettings->getTransparency());

        return $image;
    }

    private function resize(
        ?string $sourceFile,
        ?string $destinationFile,
        int $horizontalMaxWidth,
        int $verticalMaxHeight
    ): void {
        if (null === $sourceFile || null === $destinationFile) {
            return;
        }

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

        if ($this->applyUnsharpMask) {
            // very dirty way to replace Image resource
            /* @noinspection PhpUnhandledExceptionInspection */
            $reflectionProperty = new ReflectionProperty(Image::class, 'resource');
            $reflectionProperty->setAccessible(true);
            $resource = $reflectionProperty->getValue($image);

            $updatedResource = $this->applyUnsharpMask($resource, 60, 1, 1);
            $reflectionProperty->setValue($image, $updatedResource);
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        $watermarkSettings = $this->watermarkProvider->get($this->album);
        if ($watermarkSettings->isEnabled()) {
            /* @noinspection PhpUnhandledExceptionInspection */
            $image = $this->addWatermark($imagine, $image, $watermarkSettings);
        }

        $image->save($destinationFile, [
            'jpeg_quality' => self::JPEG_QUALITY,
        ]);

        // saving tags
        $manager = new Manager(new StandardPhpFileSystem(), new StandardPhpImage(), new Binary());
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
