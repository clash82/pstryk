<?php

declare(strict_types=1);

namespace App\Payload;

use App\Entity\Item;

class ItemPayload
{
    /**
     * @var string
     *
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("id")
     */
    public $id;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("album")
     */
    private $album;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("title")
     */
    private $title;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("description")
     */
    private $description;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("slug")
     */
    private $slug;

    /**
     * @var \DateTime
     *
     * @Serializer\Type("datetime")
     * @Serializer\SerializedName("date")
     */
    private $date;


    public static function createFrom(Item $item)
    {
        $obj = new self();

        $obj->id = $item->getId();
        $obj->album = $item->getAlbum();
        $obj->title = $item->getTitle();
        $obj->description = $item->getDescription();
        $obj->slug = $item->getSlug();
        $obj->date = $item->getDate();

        return $obj;
    }
}
