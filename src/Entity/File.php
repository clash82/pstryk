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

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="name", nullable=false, length=191)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $name;

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
     * @ORM\Column(type="string", name="file_id", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $fileId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="extension", length=3, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(max="3")
     */
    private $extension;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="order")
     */
    private $order = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", inversedBy="files")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $itemId;

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

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId): self
    {
        $this->fileId = $fileId;

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

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getItemId(): int
    {
        return $this->itemId;
    }

    public function setItemId(int $itemId): self
    {
        $this->itemId = $itemId;

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
