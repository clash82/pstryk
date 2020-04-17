<?php

declare(strict_types=1);

namespace App\Controller;

use App\Provider\ItemProvider;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

        $items = $this->itemProvider->getAllByAlbum(
            $slug,
            $album['type'] === 'list' ? null : $album['pagination_limit'],
            $album['type'] === 'list' ? null : 0
        );

        return $this->render(sprintf('album/%s/index.html.twig', $slug), [
            'items' => $items,
        ]);
    }
}
