<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\File;
use App\Entity\Item;
use App\Provider\AlbumProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FileFixtures extends Fixture implements DependentFixtureInterface
{
    const FILE_LIMIT = 3; // how many files should be generated per item

    const DEFAULT_EXTENSION = 'jpg';
    const DEFAULT_DESCRIPTION = 'Description for album %s, item %d, file %d';
    const DEFAULT_NAME = 'album_%s_item_%d_file_%d.jpg';

    /**
     * @var AlbumProvider
     */
    private $albumProvider;

    public function __construct(AlbumProvider $albumProvider)
    {
        $this->albumProvider = $albumProvider;
    }

    public function load(ObjectManager $manager): void
    {
        $albums = $this->albumProvider->getAll();

        foreach ($albums as $album => $v) {
            for ($item = 1; $item <= ItemFixtures::ITEM_LIMIT; ++$item) {
                for ($i = 1; $i <= self::FILE_LIMIT; ++$i) {
                    /** @var Item $itemReference */
                    $itemReference = $this->getReference(sprintf(ItemFixtures::ITEM_REFERENCE, $album, $item));
                    $fileId = sha1(sprintf('%s-%d-%d', $album, $item, $i));

                    $file = (new File())
                        ->setItem($itemReference)
                        ->setExtension(self::DEFAULT_EXTENSION)
                        ->setDescription(sprintf(self::DEFAULT_DESCRIPTION, $album, $item, $i))
                        ->setName(sprintf(self::DEFAULT_NAME, $album, $item, $i))
                        ->setFilename($fileId)
                        ->setPosition($i);

                    $manager->persist($file);
                    $manager->flush();
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
}
