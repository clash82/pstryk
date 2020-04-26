<?php

declare(strict_types=1);

namespace App\Controller;

use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AbstractController
{
    /**
     * @var ItemProvider
     */
    private $itemProvider;

    /**
     * @var AlbumProvider
     */
    private $albumProvider;

    public function __construct(ItemProvider $itemProvider, AlbumProvider $albumProvider)
    {
        $this->itemProvider = $itemProvider;
        $this->albumProvider = $albumProvider;
    }

    /**
     * @Route("/{slug}/feed", name="app_feed_index")
     */
    public function index(string $slug): Response
    {
        if (!$this->albumProvider->slugExists($slug)) {
            return $this->redirectToRoute('app_home_index');
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        $items = $this->itemProvider->getAllByAlbum(
            $slug,
            $this->albumProvider->getBySlug($slug)->getFeedLimit()
        );

        return $this->render('feed/index.html.twig', [
            'items' => $items,
        ]);
    }
}
