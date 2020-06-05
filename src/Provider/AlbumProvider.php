<?php

declare(strict_types=1);

namespace App\Provider;

use App\Exception\AlbumSettingsNotFoundException;
use App\Exception\DomainsSettingsNotFoundException;
use App\Value\Album;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AlbumProvider
{
    /** @var Album[] */
    private $albums = [];

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $albums = $parameterBag->get('app')['albums'];

        foreach ($albums as $album => $settings) {
            /* @noinspection PhpUnhandledExceptionInspection */
            $this->albums[] = new Album($album, $settings);
        }
    }

    /**
     * @return Album[]
     */
    public function getAll(): array
    {
        return $this->albums;
    }

    public function slugExists(string $id): bool
    {
        foreach ($this->albums as $album) {
            if ($album->getSlug() === $id) {
                return true;
            }
        }

        return false;
    }

    public function getBySlug(string $slug): Album
    {
        foreach ($this->albums as $album) {
            if ($album->getSlug() === $slug) {
                return $album;
            }
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        throw new AlbumSettingsNotFoundException();
    }

    public function domainExists(string $domain): bool
    {
        foreach ($this->albums as $album) {
            foreach ($album->getDomains()->getAll() as $supportedDomain) {
                if ($supportedDomain === $domain) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getByDomain(string $domain): Album
    {
        foreach ($this->albums as $album) {
            foreach ($album->getDomains()->getAll() as $supportedDomain) {
                if ($supportedDomain === $domain) {
                    return $album;
                }
            }
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        throw new DomainsSettingsNotFoundException();
    }
}
