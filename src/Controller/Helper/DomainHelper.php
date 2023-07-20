<?php declare(strict_types=1);

namespace App\Controller\Helper;

use App\Exception\DomainNotSupportedException;
use App\Provider\AlbumProvider;
use App\Value\Album;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DomainHelper extends AbstractController
{
    public function __construct(private AlbumProvider $albumProvider)
    {
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
