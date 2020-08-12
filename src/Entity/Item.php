<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Id;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="pstryk_item",
 *     indexes={@Index(name="slug_idx", columns={"slug"})}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 */
class Item
{
    use Id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="album", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $album;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="title", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", name="description", nullable=true)
     * @Assert\Type(type="string")
     */
    private $description;

    /**
     * @var string|null
     *
     * @Gedmo\Slug(fields={"title"}, updatable=false, separator="-")
     * @ORM\Column(name="slug", nullable=true, length=191, unique=true)
     * @Assert\Type(type="string")
     */
    private $slug;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="date", nullable=false)
     */
    private $date;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", name="latitude", precision=10, scale=8, nullable=true)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", name="longitude", precision=11, scale=8, nullable=true)
     */
    private $longitude;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_active", nullable=false)
     */
    private $isActive = true;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Image", mappedBy="item", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $images;

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

    public function getMainImage(): ?Image
    {
        /** @var Image $image */
        foreach ($this->images as $image) {
            if ($image->getIsMain()) {
                return $image;
            }
        }

        if (\count($this->images) > 0) {
            return $this->images[0];
        }

        return null;
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

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function getLatitude(): float
    {
        return (float) $this->latitude;
    }

    public function getLongitude(): float
    {
        return (float) $this->longitude;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getGuid(): string
    {
        return sprintf('%s_%s', $this->getAlbum() ?? '', md5((string) $this->getId()));
    }
}
