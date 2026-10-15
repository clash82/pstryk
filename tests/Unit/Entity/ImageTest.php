<?php declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function testNewImageCanBeReadWithoutErrors(): void
    {
        $image = new Image();

        self::assertSame('', $image->getName());
        self::assertSame('', (string) $image);
        self::assertNull($image->getFilename());
        self::assertNull($image->getFilePath());
        self::assertTrue($image->isEmpty());
    }

    public function testImageWithStoredFileIsNotEmpty(): void
    {
        $image = new Image()->setName('photo.jpg')->setFilename('abc')->setExtension('jpg');

        self::assertFalse($image->isEmpty());
    }
}
