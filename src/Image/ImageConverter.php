<?php

declare(strict_types=1);

namespace App\Image;

use App\Entity\Image as ImageEntity;
use App\Exception\AlbumNotSpecifiedException;
use App\Exception\FileNotExistsException;
use App\Value\Album;
use App\Value\Enum\WatermarkPosition;
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
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageConverter
{
    use UnsharpMaskTrait;

    const JPEG_QUALITY = 93;

    /** @var Album */
    private $album = null;

    /** @var bool */
    private $applyUnsharpMask = true;

    /** @var array */
    private $watermark = [];

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var Packages */
    private $packages;

    public function __construct(ParameterBagInterface $parameterBag, Packages $packages)
    {
        $this->parameterBag = $parameterBag;
        $this->packages = $packages;
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
        ImageInterface $image
    ): ImageInterface {
        if (!isset($this->watermark[$this->album->getSlug()])) {
            $watermarkFile = sprintf(
                '%s/public_html%s',
                $this->parameterBag->get('kernel.project_dir'),
                $this->packages->getUrl(sprintf(
                    'assets/images/watermark/%s',
                    $this->album->getWatermark()->getFile()
                ))
            );

            if (!file_exists($watermarkFile)) {
                /* @noinspection PhpUnhandledExceptionInspection */
                throw new FileNotExistsException($watermarkFile);
            }

            $watermark = $imagine->open($watermarkFile);
            $watermarkSize = $watermark->getSize();

            if ($this->album->getWatermark()->getWidth() > 0 && $watermarkSize->getWidth() > $this->album->getWatermark()->getWidth()
                || $this->album->getWatermark()->getHeight() > 0 && $watermarkSize->getHeight() > $this->album->getWatermark()->getHeight()) {
                $watermarkNewSize = new Box($this->album->getWatermark()->getWidth(), $this->album->getWatermark()->getHeight());
                $watermark->resize($watermarkNewSize);
            }

            $this->watermark[$this->album->getSlug()] = $watermark;
        }

        $imageSize = $image->getSize();
        $watermarkSize = $this->watermark[$this->album->getSlug()]->getSize();

        $position = new Point(
            $imageSize->getWidth() - $watermarkSize->getWidth() - $this->album->getWatermark()->getHorizontalMargin(),
            $imageSize->getHeight() - $watermarkSize->getHeight() - $this->album->getWatermark()->getVerticalMargin()
        );

        if (WatermarkPosition::POSITION_TOP_LEFT === $this->album->getWatermark()->getPosition()) {
            $position = new Point(
                $this->album->getWatermark()->getHorizontalMargin(),
                $this->album->getWatermark()->getVerticalMargin()
            );
        }

        if (WatermarkPosition::POSITION_TOP_RIGHT === $this->album->getWatermark()->getPosition()) {
            $position = new Point(
                $imageSize->getWidth() - $watermarkSize->getWidth() - $this->album->getWatermark()->getHorizontalMargin(),
                $this->album->getWatermark()->getVerticalMargin()
            );
        }

        if (WatermarkPosition::POSITION_BOTTOM_LEFT === $this->album->getWatermark()->getPosition()) {
            $position = new Point(
                $this->album->getWatermark()->getHorizontalMargin(),
                $imageSize->getHeight() - $watermarkSize->getHeight() - $this->album->getWatermark()->getVerticalMargin()
            );
        }

        $image->paste($this->watermark[$this->album->getSlug()], $position, $this->album->getWatermark()->getTransparency());

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
            $newSize = new Box($horizontalMaxWidth, $verticalMaxHeight);

            // horizontal
            if ($horizontalMaxWidth > 0 && $size->getWidth() > $size->getHeight()) {
                $newSize = new Box(
                    (int) ($size->getWidth() * ($horizontalMaxWidth / $size->getWidth())),
                    (int) ($size->getHeight() * ($horizontalMaxWidth / $size->getWidth()))
                );
            }

            // vertical
            if ($verticalMaxHeight > 0 && $size->getWidth() < $size->getHeight()) {
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
        if ($this->album->getWatermark()->isEnabled()) {
            /* @noinspection PhpUnhandledExceptionInspection */
            $image = $this->addWatermark($imagine, $image);
        }

        $image->save($destinationFile, [
            'jpeg_quality' => self::JPEG_QUALITY,
        ]);

        // saving tags
        $manager = new Manager(new StandardPhpFileSystem(), new StandardPhpImage(), new Binary());
        $manager->loadFile($destinationFile);

        /* @noinspection PhpUnhandledExceptionInspection */
        $manager->addTag(new Tag(Tag::AUTHOR, [$this->album->getTags()->getAuthor()]));
        $manager->addTag(new Tag(Tag::COPYRIGHT_STRING, [$this->album->getTags()->getCopyright()]));
        $manager->addTag(new Tag(Tag::DESCRIPTION, [$this->album->getTags()->getDescription()]));

        try {
            $manager->write();
        } catch (\Exception $e) {
            // should be fine, but in any case we'll just skip
        }
    }
}
