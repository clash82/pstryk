<?php declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Image;
use App\Entity\Item;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    public function testImageIsPublicByDefault(): void
    {
        $this->assertTrue((new Image())->getIsPublic());
    }

    public function testMainImageIsAlwaysPublic(): void
    {
        $image = (new Image())->setIsPublic(false)->setIsMain(true);
        $this->assertTrue($image->getIsPublic());

        $image->setIsPublic(false);
        $this->assertTrue($image->getIsPublic());
    }

    public function testNonPublicImagesAreHidden(): void
    {
        $hidden = (new Image())->setIsPublic(false);
        $visible = (new Image());
        $item = (new Item())->addImage($hidden)->addImage($visible);

        $this->assertSame([$visible], $item->getPublicImages());
        $this->assertSame($visible, $item->getMainImage());
    }

    public function testNoMainImageWhenAllHidden(): void
    {
        $item = (new Item())->addImage((new Image())->setIsPublic(false));

        $this->assertNull($item->getMainImage());
    }
}
