<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Item;
use App\Provider\AlbumProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ItemFixtures extends Fixture
{
    const ITEM_REFERENCE = '%s_item_%d';
    const ITEM_LIMIT = 12;

    const DEFAULT_TITLE = '%s item title %d';
    const DEFAULT_DESCRIPTION = '%s description for item %d';
    const DEFAULT_SLUG = '%s-item-slug-%d';
    const DEFAULT_LATITUDE = 50.31406826596857;
    const DEFAULT_LONGITUDE = 19.119177460670475;

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
            for ($i = 1; $i <= self::ITEM_LIMIT; ++$i) {
                $item = (new Item())
                    ->setAlbum($album)
                    ->setDate((new \DateTime())->modify(sprintf('+%d day', $i)))
                    ->setTitle(sprintf(self::DEFAULT_TITLE, $album, $i))
                    ->setDescription(sprintf(self::DEFAULT_DESCRIPTION, $album, $i))
                    ->setSlug(sprintf(self::DEFAULT_SLUG, $album, $i))
                    ->setLatitude(self::DEFAULT_LATITUDE)
                    ->setLongitude(self::DEFAULT_LONGITUDE)
                    ->setIsActive(true);

                $manager->persist($item);
                $manager->flush();

                $this->addReference(sprintf(self::ITEM_REFERENCE, $album, $i), $item);
            }
        }
    }
}
