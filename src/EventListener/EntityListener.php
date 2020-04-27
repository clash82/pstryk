<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\File;
use App\Entity\Item;
use App\Provider\StoragePathProvider;
use Doctrine\ORM\Event\LifecycleEventArgs;

class EntityListener
{
    /** @var StoragePathProvider */
    private $storagePathProvider;

    /** @var array */
    private $filesToDelete = [];

    public function __construct(StoragePathProvider $storagePathProvider)
    {
        $this->storagePathProvider = $storagePathProvider;
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $this->injectStoragePathProvider($args->getEntity());
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->injectStoragePathProvider($args->getEntity());
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        // cache assigned files to remove (those will be deleted in postRemove event)
        if ($entity instanceof Item) {
            foreach ($entity->getFiles() as $file) {
                $this->filesToDelete[] = $file;
            }
        }

        // cache assigned files to remove (those will be deleted in postRemove event)
        if ($entity instanceof File) {
            $this->filesToDelete[] = $entity;
        }
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        // we should remove assigned files only after successful entity removal
        foreach ($this->filesToDelete as $file) {
            @unlink($file->getRawRelativePath());
            @unlink($file->getThumbsRelativePath());
            @unlink($file->getImagesRelativePath());
        }

        $this->filesToDelete = [];
    }

    private function injectStoragePathProvider(object $entity): void
    {
        if ($entity instanceof File && method_exists($entity, 'setStoragePathProvider')) {
            $entity->setStoragePathProvider($this->storagePathProvider);
        }
    }
}
