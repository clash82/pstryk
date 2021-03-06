<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Item;
use App\Image\ImageConverter;
use App\Provider\AlbumProvider;
use App\Provider\StoragePathProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Palette\Color\RGB;
use Imagine\Image\Palette\RGB as PaletteRGB;
use Imagine\Image\Point;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    const FILE_LIMIT = 5; // how many files should be generated per item

    const DEFAULT_EXTENSION = 'jpg';
    const DEFAULT_DESCRIPTION = 'Description for album %s, item %d, file %d';
    const DEFAULT_NAME = 'album_%s_item_%d_file_%d.jpg';
    const DEFAULT_IMAGE_WIDTH = 1600;
    const DEFAULT_IMAGE_HEIGHT = 800;
    const DEFAULT_IMAGE_COLOR = '6F890F';
    const DEFAULT_FONT_PATH = 'c:\windows\fonts\tahoma.ttf';
    const DEFAULT_FONT_SIZE = 30;
    const DEFAULT_FONT_COLOR = [255, 255, 255];

    /** @var AlbumProvider */
    private $albumProvider;

    /** @var ImageConverter */
    private $imageConverter;

    /** @var StoragePathProvider */
    private $storagePathProvider;

    /** @var bool */
    private $populateImages = true;

    public function __construct(
        AlbumProvider $albumProvider,
        StoragePathProvider $storagePathProvider,
        ImageConverter $imageConverter,
        ParameterBagInterface $parameterBag
    ) {
        $this->albumProvider = $albumProvider;
        $this->imageConverter = $imageConverter;
        $this->storagePathProvider = $storagePathProvider;

        if ('test' === $parameterBag->get('kernel.environment')) {
            $this->populateImages = false;
        }
    }

    public function load(ObjectManager $manager): void
    {
        if ($this->populateImages) {
            $this->removeFiles($this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_RAW));
            $this->removeFiles($this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_IMAGES));
            $this->removeFiles($this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_THUMBS));
        }

        $albums = $this->albumProvider->getAll();
        $this->imageConverter->setApplyUnsharpMask(false);

        foreach ($albums as $album) {
            $this->imageConverter->setAlbum($album);

            for ($item = 1; $item <= ItemFixtures::ITEM_LIMIT; ++$item) {
                for ($i = 1; $i <= self::FILE_LIMIT; ++$i) {
                    $slug = $album->getSlug();

                    /** @var Item $itemReference */
                    $itemReference = $this->getReference(sprintf(ItemFixtures::ITEM_REFERENCE, $slug, $item));
                    $fileId = sha1(sprintf('%s-%d-%d', $slug, $item, $i));

                    $file = (new Image())
                        ->setItem($itemReference)
                        ->setExtension(self::DEFAULT_EXTENSION)
                        ->setDescription(sprintf(self::DEFAULT_DESCRIPTION, $slug, $item, $i))
                        ->setName(sprintf(self::DEFAULT_NAME, $slug, $item, $i))
                        ->setFilename($fileId)
                        ->setIsMain(2 === $i)
                        ->setPosition($i);

                    $manager->persist($file);
                    $manager->flush();

                    $filename = sprintf(
                        '%s/%s.%s',
                        $this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_RAW),
                        $file->getFilename(),
                        $file->getExtension()
                    );

                    if ($this->populateImages) {
                        $this->createImage($filename, $file->getName());
                        /* @noinspection PhpUnhandledExceptionInspection */
                        $this->imageConverter->convert($file);
                    }
                }
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            ItemFixtures::class,
        ];
    }

    private function createImage(string $filename, ?string $text): void
    {
        $imagine = new Imagine();
        $palette = new PaletteRGB();

        $image = $imagine->create(
            new Box(self::DEFAULT_IMAGE_WIDTH, self::DEFAULT_IMAGE_HEIGHT),
            $palette->color(self::DEFAULT_IMAGE_COLOR, 100)
        );

        if (null !== $text) {
            $image->draw()->text(
                $text,
                new Font(self::DEFAULT_FONT_PATH, self::DEFAULT_FONT_SIZE, new RGB($palette, self::DEFAULT_FONT_COLOR, 100)),
                new Point(15, 15)
            );
        }

        $image->save($filename, [
            'jpeg_quality' => 90,
        ]);
    }

    private function removeFiles(string $path): void
    {
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        ) as $filename) {
            if (!$filename->isDir()
                && '.gitkeep' !== $filename->getFileName()
                && '.htaccess' !== $filename->getFileName()) {
                unlink(sprintf('%s/%s', $path, $filename->getFileName()));
            }
        }
    }
}
