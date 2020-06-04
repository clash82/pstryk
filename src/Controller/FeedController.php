<?php

declare(strict_types=1);

namespace App\Controller;

use App\Provider\AlbumSettingsProvider;
use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AbstractController
{
    /** @var ItemProvider */
    private $itemProvider;

    /** @var AlbumSettingsProvider */
    private $albumProvider;

    public function __construct(ItemProvider $itemProvider, AlbumSettingsProvider $albumProvider)
    {
        $this->itemProvider = $itemProvider;
        $this->albumProvider = $albumProvider;
    }

    /**
     * @Route("/{albumSlug}/feed", name="app_feed_index", defaults={"_format"="xml"})
     */
    public function index(string $albumSlug): Response
    {
        if (!$this->albumProvider->slugExists($albumSlug)) {
            return $this->redirectToRoute('app_home_index');
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        $items = $this->itemProvider->getAllPaginated(
            $albumSlug,
            1,
            $this->albumProvider->getBySlug($albumSlug)->getFeedLimit()
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render('feed/index.html.twig', [
            'items' => $items,
            'album' => $this->albumProvider->getBySlug($albumSlug),
        ]);
    }
}
