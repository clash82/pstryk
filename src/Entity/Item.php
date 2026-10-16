<?php declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Id;
use App\Repository\ItemRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'pstryk_item')]
#[ORM\Index(columns: ['slug'], name: 'slug_idx')]
#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    use Id;

    #[ORM\Column(name: 'album', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    private ?string $album = null;

    #[ORM\Column(name: 'title', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    private string $title;

    #[ORM\Column(name: 'description', type: 'string', nullable: true)]
    #[Assert\Type(type: 'string')]
    private ?string $description = null;

    #[Gedmo\Slug(fields: ['title'], updatable: false, separator: '-')]
    #[ORM\Column(name: 'slug', length: 191, unique: true, nullable: true)]
    #[Assert\Type(type: 'string')]
    private ?string $slug = null;

    #[ORM\Column(name: 'date', type: 'datetime', nullable: false)]
    private DateTime $date;

    #[ORM\Column(name: 'latitude', type: 'decimal', precision: 10, scale: 8, nullable: true)]
    /**
     * DBAL maps "decimal" columns to strings (to avoid float precision loss), so the
     * property is a string here and converted to float in the accessors.
     */
    private ?string $latitude = null;

    #[ORM\Column(name: 'longitude', type: 'decimal', precision: 11, scale: 8, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', nullable: false)]
    private bool $isActive = true;

    #[ORM\OneToMany(mappedBy: 'item', targetEntity: Image::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->date = new DateTime();
    }

    public function addImage(Image $image): self
    {
        $image->setItem($this);

        $this->images[] = $image;

        return $this;
    }

    public function removeImage(Image $image): void
    {
        $this->images->removeElement($image);
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @return Image[]
     */
    public function getPublicImages(): array
    {
        return $this->images->filter(static fn (Image $image): bool => $image->getIsPublic())->getValues();
    }

    /**
     * @return Image[]
     */
    public function getVisibleImages(bool $includeHidden = false): array
    {
        return $includeHidden ? $this->images->getValues() : $this->getPublicImages();
    }

    public function getMainImage(bool $includeHidden = false): ?Image
    {
        $images = $this->getVisibleImages($includeHidden);

        foreach ($images as $image) {
            if ($image->getIsMain()) {
                return $image;
            }
        }

        return $images[0] ?? null;
    }

    public function setAlbum(string $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = null === $latitude ? null : \sprintf('%.8F', $latitude);

        return $this;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = null === $longitude ? null : \sprintf('%.8F', $longitude);

        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAlbum(): ?string
    {
        return $this->album;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function getLatitude(): ?float
    {
        return null === $this->latitude ? null : (float) $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return null === $this->longitude ? null : (float) $this->longitude;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getGuid(): string
    {
        return \sprintf('%s_%s', $this->getAlbum() ?? '', md5((string) $this->getId()));
    }
}
