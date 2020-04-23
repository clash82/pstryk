<?php

declare(strict_types=1);

namespace App\Controller;

use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    private $itemProvider;

    public function __construct(ItemProvider $itemProvider)
    {
        $this->itemProvider = $itemProvider;
    }

    /**
     * @Route("/{slug}", name="app_album_index")
     */
    public function index(string $slug)
    {
        $albums = $this->getParameter('app')['albums'];

        if (!isset($albums[$slug])) {
            return $this->redirectToRoute('app_home_index');
        }

        $album = $albums[$slug];

        /*
        $items = $this->itemProvider->getAllByAlbum(
            $slug,
            'list' === $album['type'] ? null : $album['pagination_limit'],
            'list' === $album['type'] ? null : 0
        );
        */

        return $this->render(sprintf('album/%s/index.html.twig', $slug), [
            'items' => [],
        ]);
    }
}
