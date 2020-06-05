<?php

declare(strict_types=1);

namespace App\Controller\Helper;

use App\Exception\DomainNotSupportedException;
use App\Provider\AlbumProvider;
use App\Value\Album;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DomainHelper extends AbstractController
{
    /** @var AlbumProvider */
    private $albumProvider;

    public function __construct(AlbumProvider $albumProvider)
    {
        $this->albumProvider = $albumProvider;
    }

    public function getCurrentAlbum(): Album
    {
        $domain = $_SERVER['SERVER_NAME'];

        if (!$this->albumProvider->domainExists($domain)) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new DomainNotSupportedException($domain);
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->albumProvider->getByDomain($domain);
    }
}
