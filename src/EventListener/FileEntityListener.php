<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileEntityListener
{
    /** @var string */
    private $storageRawPath = '';

    /** @var string */
    private $storageThumbsPath = '';

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->storageRawPath = $parameterBag->get('app')['storage_raw_path'];
        $this->storageThumbsPath = $parameterBag->get('app')['storage_thumbs_path'];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (method_exists($entity, 'setStorageRawPath')) {
            $entity->setStorageRawPath($this->storageRawPath);
        }

        if (method_exists($entity, 'setStorageThumbsPath')) {
            $entity->setStorageThumbsPath($this->storageThumbsPath);
        }
    }
}
