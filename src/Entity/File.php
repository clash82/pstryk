<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Id;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="pstryk_file",
 *     indexes={@Index(name="name_idx", columns={"name"})}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File
{
    use Id;

    const PATH_RELATIVE_PATTERN = '%s%s/%s.%s';
    const PATH_PUBLIC_PATTERN = '%s/%s.%s';

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=191, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(max=191)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     * @Assert\Type(type="string")
     * @Assert\Length(max=255)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="filename", length=40, nullable=false, unique=true)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(max=40)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="extension", length=3, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(max=3)
     */
    private $extension;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="position")
     */
    private $position;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="files")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $item;

    /** @var string */
    private $storageRawPath = '';

    /** @var string */
    private $storageThumbsPath = '';

    /** @var string */
    private $storageImagesPath = '';

    public function setStorageRawPath(string $storageRawPath): void
    {
        $this->storageRawPath = $storageRawPath;
    }

    public function setStorageThumbsPath(string $storageThumbsPath): void
    {
        $this->storageThumbsPath = $storageThumbsPath;
    }

    public function setStorageImagesPath(string $storageImagesPath): void
    {
        $this->storageImagesPath = $storageImagesPath;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getRawRelativePath(): string
    {
        return sprintf(
            self::PATH_RELATIVE_PATTERN,
            getcwd(),
            $this->storageRawPath,
            $this->filename,
            $this->extension
        );
    }

    public function getRawPublicPath(): string
    {
        return sprintf(
            self::PATH_PUBLIC_PATTERN,
            $this->storageRawPath,
            $this->filename,
            $this->extension
        );
    }

    public function getThumbsRelativePath(): string
    {
        return sprintf(
            self::PATH_RELATIVE_PATTERN,
            getcwd(),
            $this->storageThumbsPath,
            $this->filename,
            $this->extension
        );
    }

    public function getThumbsPublicPath(): string
    {
        return sprintf(
            self::PATH_PUBLIC_PATTERN,
            $this->storageThumbsPath,
            $this->filename,
            $this->extension
        );
    }

    public function getImagesRelativePath(): string
    {
        return sprintf(
            self::PATH_RELATIVE_PATTERN,
            getcwd(),
            $this->storageImagesPath,
            $this->filename,
            $this->extension
        );
    }

    public function getImagesPublicPath(): string
    {
        return sprintf(
            self::PATH_PUBLIC_PATTERN,
            $this->storageImagesPath,
            $this->filename,
            $this->extension
        );
    }
}
