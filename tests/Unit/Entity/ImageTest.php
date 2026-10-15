<?php declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Image;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class ImageTest extends TestCase
{
    public function testEmptyImageCanBeReadWithoutErrors(): void
    {
        $image = new Image();

        self::assertSame('', $image->getName());
        self::assertSame('', (string) $image);
        self::assertNull($image->getFilename());
        self::assertNull($image->getFilePath());
    }

    public function testImageWithoutFileIsInvalid(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new Image());

        self::assertGreaterThan(0, $violations->count());
    }

    public function testImageWithStoredFileIsValid(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $image = (new Image())->setName('photo.jpg')->setFilename('abc')->setExtension('jpg');

        self::assertCount(0, $validator->validate($image));
    }
}
