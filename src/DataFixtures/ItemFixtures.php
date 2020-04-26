<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ItemFixtures extends Fixture
{
    const DEFAULT_ALBUM = 'stalker';
    const DEFAULT_TITLE = 'Item title %d';
    const DEFAULT_DESCRIPTION = 'Description for item %d';
    const DEFAULT_SLUG = 'item-slug-%d';
    const DEFAULT_LATITUDE = 50.31406826596857;
    const DEFAULT_LONGITUDE = 19.119177460670475;

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 50; ++$i) {
            $manager->persist((new Item())
                ->setAlbum(self::DEFAULT_ALBUM)
                ->setDate((new \DateTime())->modify(sprintf('+%d day', $i)))
                ->setTitle(sprintf(self::DEFAULT_TITLE, $i))
                ->setDescription(sprintf(self::DEFAULT_DESCRIPTION, $i))
                ->setSlug(sprintf(self::DEFAULT_SLUG, $i))
                ->setLatitude(self::DEFAULT_LATITUDE)
                ->setLongitude(self::DEFAULT_LONGITUDE)
                ->setIsActive(true));
        }

        $manager->flush();
    }
}
