<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Image;
use App\Entity\Item;
use App\Image\ImageConverter;
use App\Provider\AlbumProvider;
use App\Provider\StoragePathProvider;
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
            // in some cases it might happen that file not exists (mostly in dev mode
            // when performing tests) - should not occur in normal usage
            if (!$file->getFilePath()->getRawRelativePath()) {
                continue;
            }

            if (file_exists($file->getFilePath()->getRawRelativePath())) {
                unlink($file->getFilePath()->getRawRelativePath());
            }

            if (file_exists($file->getFilePath()->getThumbRelativePath())) {
                unlink($file->getFilePath()->getThumbRelativePath());
            }

            if (file_exists($file->getFilePath()->getImageRelativePath())) {
                unlink($file->getFilePath()->getImageRelativePath());
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
