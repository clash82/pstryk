<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Id;
use App\Image\ImageConverter;
use App\Provider\StoragePathProvider;
use App\Value\FilePath;
use App\Value\ImageDetails;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="pstryk_image",
 *     indexes={@Index(name="name_idx", columns={"name"})}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image
{
    use Id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=191, nullable=true)
     * @Assert\Type(type="string")
     * @Assert\Length(max=191)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     * @Assert\Type(type="string")
     * @Assert\Length(max=255)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="filename", length=40, nullable=true, unique=true)
     * @Assert\Type(type="string")
     * @Assert\Length(max=40)
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="extension", length=3, nullable=true)
     * @Assert\Type(type="string")
     * @Assert\Length(max=3)
     */
    private $extension;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_main", nullable=false)
     */
    private $isMain = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="position")
     */
    private $position = 0;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="images")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $item;

    /** @var StoragePathProvider */
    private $storagePathProvider;

    /** @var ImageConverter */
    private $imageConverter;

    /** @var UploadedFile|null */
    private $file;

    /** @var array */
    private $filesToRemove = [];

    /** @var FilePath */
    private $filePath;

    /** @var ImageDetails */
    private $imageDetails;

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Triggered by EntityListener.
     */
    public function setStoragePathProvider(StoragePathProvider $storagePathProvider): void
    {
        $this->storagePathProvider = $storagePathProvider;
    }

    /**
     * Triggered by EntityListener.
     */
    public function setImageConverter(ImageConverter $imageConverter): void
    {
        $this->imageConverter = $imageConverter;
    }

    /**
     * Triggered by EntityListener.
     */
    public function upload(): void
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move(
            $this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_RAW),
            sprintf('%s.%s', $this->filename, $this->extension)
        );

        $this->file = null;

        // we have to recreate object to generate new file paths
        $this->filePath = new FilePath($this->storagePathProvider, $this->filename, $this->extension);

        /* @noinspection PhpUnhandledExceptionInspection */
        $this->imageConverter->convert($this);

        // we're overriding existing file so file id is being regenerated, now we should remove old files
        foreach ($this->filesToRemove as $oldFile) {
            @unlink($oldFile);
        }
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $uploadedFile): self
    {
        $this->file = $uploadedFile;

        /* @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal */
        if (null !== $this->getFilePath() && file_exists($this->getFilePath()->getRawRelativePath())) {
            // we want to update existing file, so we need to save a list of files
            // to be removed upon uploading process start
            $this->filesToRemove = [
                $this->getFilePath()->getRawRelativePath(),
                $this->getFilePath()->getThumbRelativePath(),
                $this->getFilePath()->getImageRelativePath(),
            ];
        }

        $this->name = empty($uploadedFile->getClientOriginalName()) ? '' : $uploadedFile->getClientOriginalName();
        $this->extension = $uploadedFile->getClientOriginalExtension();
        $this->filename = sha1(sprintf(
            '%d-%s-%s',
            rand(1, 1000),
            uniqid(),
            $this->name
        ));

        return $this;
    }

    public function getFilePath(): ?FilePath
    {
        if (!$this->extension) {
            return null;
        }

        if (null === $this->filePath) {
            $this->filePath = new FilePath($this->storagePathProvider, $this->filename, $this->extension);
        }

        return $this->filePath;
    }

    public function getImageDetails(): ?ImageDetails
    {
        $filePath = $this->getFilePath();

        if (null === $filePath) {
            return null;
        }

        if (null === $this->imageDetails) {
            $this->imageDetails = new ImageDetails($this->filePath);
        }

        return $this->imageDetails;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
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
        $this->extension = strtolower($extension);

        return $this;
    }

    public function getIsMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;

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
}
