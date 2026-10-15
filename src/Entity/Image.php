<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Id;
use App\Image\ImageConverter;
use App\Image\PendingUploadedFile;
use App\Provider\StoragePathProvider;
use App\Repository\ImageRepository;
use App\Value\FilePath;
use App\Value\ImageDetails;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'pstryk_image')]
#[ORM\Index(columns: ['name'], name: 'name_idx')]
#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image implements \Stringable
{
    use Id;

    #[ORM\Column(name: 'name', type: 'string', length: 191, nullable: false)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 191)]
    private string $name = '';

    #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    private ?string $description = null;

    #[ORM\Column(name: 'filename', type: 'string', length: 40, unique: true, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 40)]
    private ?string $filename = null;

    #[ORM\Column(name: 'extension', type: 'string', length: 3, nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 3)]
    private ?string $extension = null;

    #[ORM\Column(name: 'is_main', type: 'boolean', nullable: false)]
    private bool $isMain = false;

    #[ORM\Column(name: 'is_public', type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $isPublic = true;

    #[ORM\Column(name: 'position', type: 'integer')]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Item $item;

    private StoragePathProvider $storagePathProvider;

    private ImageConverter $imageConverter;

    private ?UploadedFile $file = null;

    private array $filesToRemove = [];

    private ?FilePath $filePath = null;

    private ?ImageDetails $imageDetails = null;

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
        if (!$this->file instanceof UploadedFile) {
            return;
        }

        $uploadedFile = $this->file;

        $uploadedFile->move(
            $this->storagePathProvider->getRelativeDir(StoragePathProvider::PATH_RAW),
            \sprintf('%s.%s', $this->filename, $this->extension)
        );

        $this->file = null;

        // we have to recreate object to generate new file paths
        $this->filePath = new FilePath($this->storagePathProvider, $this->filename, $this->extension);

        if ($uploadedFile instanceof PendingUploadedFile && $uploadedFile->isProcessedFor($this->item->getAlbum())) {
            // the image was already converted when it was dropped on the form, no need to do it again
            $uploadedFile->moveProcessed(
                $this->filePath->getImageRelativePath(),
                $this->filePath->getThumbRelativePath()
            );
        } else {
            /* @noinspection PhpUnhandledExceptionInspection */
            $this->imageConverter->convert($this);
        }

        // we're overriding existing file so file id is being regenerated, now we should remove old files
        foreach ($this->filesToRemove as $oldFile) {
            if (!file_exists($oldFile)) {
                continue;
            }

            unlink($oldFile);
        }
    }

    /**
     * An image is empty when it has neither a freshly uploaded file nor an already stored one.
     */
    public function isEmpty(): bool
    {
        return !$this->file instanceof UploadedFile && !$this->filename;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $uploadedFile): self
    {
        $this->file = $uploadedFile;

        if ($this->getFilePath() instanceof FilePath && file_exists($this->getFilePath()->getRawRelativePath())) {
            // we want to update existing file, so we need to save a list of files
            // to be removed upon uploading process start
            $this->filesToRemove = [
                $this->getFilePath()->getRawRelativePath(),
                $this->getFilePath()->getThumbRelativePath(),
                $this->getFilePath()->getImageRelativePath(),
            ];
        }

        $this->extension = $uploadedFile->getClientOriginalExtension();
        $this->filename = sha1(\sprintf(
            '%d-%s-%s',
            random_int(1, 1000),
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

        if (!$this->filePath instanceof FilePath) {
            $this->filePath = new FilePath($this->storagePathProvider, $this->filename, $this->extension);
        }

        return $this->filePath;
    }

    public function getImageDetails(): ?ImageDetails
    {
        $filePath = $this->getFilePath();

        if (!$filePath instanceof FilePath) {
            return null;
        }

        if (!$this->imageDetails instanceof ImageDetails) {
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

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getExtension(): ?string
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

        if ($isMain) {
            // cover image must always be public
            $this->isPublic = true;
        }

        return $this;
    }

    public function getIsPublic(): bool
    {
        return $this->isMain || $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $this->isMain || $isPublic;

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
