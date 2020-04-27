<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\File;
use App\Entity\Item;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EntityListener
{
    /** @var string */
    private $storageRawPath = '';

    /** @var string */
    private $storageThumbsPath = '';

    /** @var array */
    private $filesToDelete = [];

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->storageRawPath = $parameterBag->get('app')['storage_raw_path'];
        $this->storageThumbsPath = $parameterBag->get('app')['storage_thumbs_path'];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof File) {
            if (method_exists($entity, 'setStorageRawPath')) {
                $entity->setStorageRawPath($this->storageRawPath);
            }

            if (method_exists($entity, 'setStorageThumbsPath')) {
                $entity->setStorageThumbsPath($this->storageThumbsPath);
            }
        }
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
        }

        $this->filesToDelete = [];
    }
}
