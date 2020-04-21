<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Id;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
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
     * @var string
     *
     * @ORM\Column(type="string", name="description", nullable=true)
     * @Assert\Type(type="string")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="slug", nullable=false, length=191)
     * @Assert\NotBlank()
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
     *  @ORM\OneToMany(targetEntity="App\Entity\File", mappedBy="itemId")
     */
    private $files;

    public function setAlbum(string $album): self
    {
        $this->album = $album;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setFiles(array $files = []): self
    {
        $this->files = $files;

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

    public function getAlbum(): string
    {
        return $this->album;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLonitude(): float
    {
        return $this->longitude;
    }
}