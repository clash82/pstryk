<?php declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\DomainHelper;
use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AbstractController
{
    public function __construct(private ItemProvider $itemProvider, private AlbumProvider $albumProvider, private DomainHelper $domainHelper)
    {
    }

    /**
     * @Route("/feed", name="app_feed_index", defaults={"_format"="xml"})
     */
    public function index(): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        /* @noinspection PhpUnhandledExceptionInspection */
        $items = $this->itemProvider->getAllPaginated(
            $album->getSlug(),
            1,
            $this->albumProvider->getBySlug($album->getSlug())->getFeedLimit()
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render('feed/index.html.twig', [
            'items' => $items,
            'album' => $this->albumProvider->getBySlug($album->getSlug()),
        ]);
    }
}
