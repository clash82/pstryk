<?php

declare(strict_types=1);

namespace App\Controller;

use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FeedController extends AbstractController
{
    private $itemProvider;

    public function __construct(ItemProvider $itemProvider)
    {
        $this->itemProvider = $itemProvider;
    }

    /**
     * @Route("/{slug}/feed", name="app_feed_index")
     */
    public function index(string $slug)
    {
        $albums = $this->getParameter('app')['albums'];

        if (!isset($albums[$slug])) {
            return $this->redirectToRoute('app_home_index');
        }

        $album = $albums[$slug];

        $items = $this->itemProvider->getAllByAlbum($slug, $album['feed_limit']);

        return $this->render('feed/index.html.twig', [
            'items' => $items,
        ]);
    }
}
