<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Image;
use App\Entity\Item;
use App\Image\ImageConverter;
use App\Provider\AlbumProvider;
use App\Provider\StoragePathProvider;
use App\Value\FilePath;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;

class EntityListener
{
    private array $filesToDelete = [];

    public function __construct(private readonly StoragePathProvider $storagePathProvider, private readonly ImageConverter $imageConverter, private readonly AlbumProvider $albumProvider)
    {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $this->injectStoragePathProvider($args->getObject());
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        $this->injectStoragePathProvider($entity);
        $this->injectImageConverter($entity);
        $this->triggerUpload($entity);
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        $this->injectStoragePathProvider($entity);
        $this->injectImageConverter($entity);
        $this->triggerUpload($entity);
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        // cache assigned files to remove (those will be deleted in postRemove event)
        if ($entity instanceof Item) {
            foreach ($entity->getImages() as $file) {
                $this->filesToDelete[] = $file;
            }
        }

        // cache assigned files to remove (those will be deleted in postRemove event)
        if ($entity instanceof Image) {
            $this->filesToDelete[] = $entity;
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        // we should remove assigned files only after successful entity removal
        foreach ($this->filesToDelete as $file) {
            $filePath = $file->getFilePath();

            // an image entity might exist without any uploaded file (no extension means no file path),
            // and in some cases the file might be missing on the disk (mostly in dev mode when performing
            // tests) - there is nothing to delete then
            if (!$filePath instanceof FilePath || !$filePath->getRawRelativePath()) {
                continue;
            }

            if (file_exists($filePath->getRawRelativePath())) {
                unlink($filePath->getRawRelativePath());
            }

            if (file_exists($filePath->getThumbRelativePath())) {
                unlink($filePath->getThumbRelativePath());
            }

            if (file_exists($filePath->getImageRelativePath())) {
                unlink($filePath->getImageRelativePath());
            }
        }

        $this->filesToDelete = [];
    }

    private function injectStoragePathProvider(object $entity): void
    {
        if ($entity instanceof Image) {
            $entity->setStoragePathProvider($this->storagePathProvider);
        }
    }

    private function injectImageConverter(object $entity): void
    {
        if ($entity instanceof Image) {
            $slug = $entity->getItem()->getAlbum();

            if ($slug) {
                /* @noinspection PhpUnhandledExceptionInspection */
                $this->imageConverter->setAlbum(
                    $this->albumProvider->getBySlug($slug)
                );

                $entity->setImageConverter($this->imageConverter);
            }
        }
    }

    private function triggerUpload(object $entity): void
    {
        if ($entity instanceof Image) {
            $entity->upload();
        }
    }
}
