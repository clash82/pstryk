<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Item;
use App\Provider\AlbumProvider;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ItemFixtures extends Fixture
{
    public const ITEM_REFERENCE = '%s_item_%d';
    public const ITEM_LIMIT = 12;

    private const DEFAULT_TITLE = '%s item title %d';
    private const DEFAULT_DESCRIPTION = '%s description for item %d';
    private const DEFAULT_SLUG = '%s-item-slug-%d';
    private const DEFAULT_LATITUDE = 50.31406826596857;
    private const DEFAULT_LONGITUDE = 19.119177460670475;

    private AlbumProvider $albumProvider;

    public function __construct(AlbumProvider $albumProvider)
    {
        $this->albumProvider = $albumProvider;
    }

    public function load(ObjectManager $manager): void
    {
        $albums = $this->albumProvider->getAll();

        foreach ($albums as $album) {
            $slug = $album->getSlug();

            for ($i = 1; $i <= self::ITEM_LIMIT; ++$i) {
                $item = (new Item())
                    ->setAlbum($slug)
                    ->setDate((new DateTime())->modify(sprintf('+%d day', $i)))
                    ->setTitle(sprintf(self::DEFAULT_TITLE, $slug, $i))
                    ->setDescription(sprintf(self::DEFAULT_DESCRIPTION, $slug, $i))
                    ->setSlug(sprintf(self::DEFAULT_SLUG, $slug, $i))
                    ->setLatitude(self::DEFAULT_LATITUDE)
                    ->setLongitude(self::DEFAULT_LONGITUDE)
                    ->setIsActive(true);

                $manager->persist($item);
                $manager->flush();

                $this->addReference(sprintf(self::ITEM_REFERENCE, $slug, $i), $item);
            }
        }
    }
}
