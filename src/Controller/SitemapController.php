<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\DomainHelper;
use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    /** @var ItemProvider */
    private $itemProvider;

    /** @var AlbumProvider */
    private $albumProvider;

    /** @var DomainHelper */
    private $domainHelper;

    public function __construct(
        ItemProvider $itemProvider,
        AlbumProvider $albumProvider,
        DomainHelper $domainHelper
    ) {
        $this->itemProvider = $itemProvider;
        $this->albumProvider = $albumProvider;
        $this->domainHelper = $domainHelper;
    }

    /**
     * @Route("/sitemap.xml", name="app_sitemap_index", defaults={"_format"="xml"})
     */
    public function index(): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        if (false === $album->getSitemap()) {
            return $this->redirectToRoute('app_album_index');
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        $items = $this->itemProvider->getAllPaginated(
            $album->getSlug(),
            1,
            999999
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render('sitemap/index.html.twig', [
            'items' => $items,
        ]);
    }
}
