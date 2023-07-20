<?php declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Image;
use App\Entity\Item;
use App\Image\ImageConverter;
use App\Provider\AlbumProvider;
use App\Provider\StoragePathProvider;
use Doctrine\ORM\Event\LifecycleEventArgs;

class EntityListener
{
    private array $filesToDelete = [];

    public function __construct(private StoragePathProvider $storagePathProvider, private ImageConverter $imageConverter, private AlbumProvider $albumProvider)
    {
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $this->injectStoragePathProvider($args->getEntity());
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        $this->injectStoragePathProvider($entity);
        $this->injectImageConverter($entity);
        $this->triggerUpload($entity);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        $this->injectStoragePathProvider($entity);
        $this->injectImageConverter($entity);
        $this->triggerUpload($entity);
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

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

    public function postRemove(LifecycleEventArgs $args): void
    {
        // we should remove assigned files only after successful entity removal
        foreach ($this->filesToDelete as $file) {
            // in some cases it might happen that file not exists (mostly in dev mode
            // when performing tests) - should not occur in normal usage
            if (!$file->getFilePath()->getRawRelativePath()) {
                continue;
            }

            if (file_exists($file->getFilePath()->getRawRelativePath())) {
                /* @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal */
                unlink($file->getFilePath()->getRawRelativePath());
            }

            if (file_exists($file->getFilePath()->getThumbRelativePath())) {
                /* @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal */
                unlink($file->getFilePath()->getThumbRelativePath());
            }

            if (file_exists($file->getFilePath()->getImageRelativePath())) {
                /* @phan-suppress-next-line PhanTypeMismatchArgumentNullableInternal */
                unlink($file->getFilePath()->getImageRelativePath());
            }
        }

        $this->filesToDelete = [];
    }

    private function injectStoragePathProvider(object $entity): void
    {
        if ($entity instanceof Image && method_exists($entity, 'setStoragePathProvider')) {
            $entity->setStoragePathProvider($this->storagePathProvider);
        }
    }

    private function injectImageConverter(object $entity): void
    {
        if ($entity instanceof Image && method_exists($entity, 'setImageConverter')) {
            $slug = $entity->getItem()->getAlbum();

            if ($slug) {
                /* @noinspection PhpUnhandledExceptionInspection */
                $this->imageConverter->setAlbum(
                /* @phan-suppress-next-line PhanTypeMismatchArgumentNullable */
                    $this->albumProvider->getBySlug($slug)
                );

                $entity->setImageConverter($this->imageConverter);
            }
        }
    }

    private function triggerUpload(object $entity): void
    {
        if ($entity instanceof Image && method_exists($entity, 'upload')) {
            $entity->upload();
        }
    }
}
