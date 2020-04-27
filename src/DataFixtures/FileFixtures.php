<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\File;
use App\Entity\Item;
use App\Provider\AlbumProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileFixtures extends Fixture implements DependentFixtureInterface
{
    const FILE_LIMIT = 5; // how many files should be generated per item
    const PUBLIC_PATH_PATTERN = '%s/public_html/%s';

    const DEFAULT_EXTENSION = 'jpg';
    const DEFAULT_DESCRIPTION = 'Description for album %s, item %d, file %d';
    const DEFAULT_NAME = 'album_%s_item_%d_file_%d.jpg';
    const DEFAULT_IMAGE_WIDTH = 1600;
    const DEFAULT_IMAGE_HEIGHT = 800;

    /**
     * @var AlbumProvider
     */
    private $albumProvider;

    /** @var string */
    private $storageRawPath = '';

    /** @var string */
    private $storageThumbsPath = '';

    public function __construct(AlbumProvider $albumProvider, ParameterBagInterface $parameterBag)
    {
        $this->albumProvider = $albumProvider;

        $this->storageRawPath = sprintf(
            self::PUBLIC_PATH_PATTERN,
            getcwd(),
            $parameterBag->get('app')['storage_raw_path']
        );
        $this->storageThumbsPath = sprintf(
            self::PUBLIC_PATH_PATTERN,
            getcwd(),
            $parameterBag->get('app')['storage_thumbs_path']
        );
    }

    public function load(ObjectManager $manager): void
    {
        $this->removeFiles($this->storageRawPath);

        $albums = $this->albumProvider->getAll();

        foreach ($albums as $album) {
            for ($item = 1; $item <= ItemFixtures::ITEM_LIMIT; ++$item) {
                for ($i = 1; $i <= self::FILE_LIMIT; ++$i) {
                    $slug = $album->getSlug();

                    /** @var Item $itemReference */
                    $itemReference = $this->getReference(sprintf(ItemFixtures::ITEM_REFERENCE, $slug, $item));
                    $fileId = sha1(sprintf('%s-%d-%d', $slug, $item, $i));

                    $file = (new File())
                        ->setItem($itemReference)
                        ->setExtension(self::DEFAULT_EXTENSION)
                        ->setDescription(sprintf(self::DEFAULT_DESCRIPTION, $slug, $item, $i))
                        ->setName(sprintf(self::DEFAULT_NAME, $slug, $item, $i))
                        ->setFilename($fileId)
                        ->setPosition($i);

                    $manager->persist($file);
                    $manager->flush();

                    $filename = sprintf(
                        '%s/%s.%s',
                        $this->storageRawPath,
                        $file->getFilename(),
                        $file->getExtension()
                    );

                    $this->createImage($filename, $file->getName());
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

    private function createImage(string $filename, string $text): void
    {
        /** @var resource $image */
        $image = imagecreatetruecolor(self::DEFAULT_IMAGE_WIDTH, self::DEFAULT_IMAGE_HEIGHT);
        $color = imagecolorallocate($image, 15, 137, 111);
        imagefill($image, 0, 0, $color);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 100, 5, 5, $text, $textColor);
        imagejpeg($image, $filename);
        imagedestroy($image);
    }

    private function removeFiles(string $path): void
    {
        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        ) as $filename) {
            if (!$filename->isDir() && '.gitkeep' !== $filename->getFileName()) {
                unlink(sprintf('%s/%s', $path, $filename->getFileName()));
            }
        }
    }
}
