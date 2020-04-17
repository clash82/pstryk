<?php

declare(strict_types=1);

namespace App\Payload;

use App\Entity\File;

class FilePayload
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
     * @Serializer\SerializedName("storage_raw_path")
     */
    public $storageRawPath;

    public static function createFrom(File $file)
    {
        $obj = new self();

        $obj->id = $file->getId();
        $obj->storageRawPath = sprintf('%s/%s.%s', '', $file->getFileId(), $file->getExtension());

        return $obj;
    }
}
