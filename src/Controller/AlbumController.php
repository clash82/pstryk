<?php

declare(strict_types=1);

namespace App\Controller;

use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    /**
     * @var ItemProvider
     */
    private $itemProvider;

    public function __construct(ItemProvider $itemProvider)
    {
        $this->itemProvider = $itemProvider;
    }

    /**
     * @Route("/{slug}/{page}", name="app_album_index", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     */
    public function index(string $slug, int $page): Response
    {
        $albums = $this->getParameter('app')['albums'];

        if (!isset($albums[$slug])) {
            return $this->redirectToRoute('app_home_index');
        }

        $album = $albums[$slug];

        $items = $this->itemProvider->getAllByAlbum(
            $slug,
            $page,
            $album['pagination_limit']
        );

        return $this->render(sprintf('album/%s/index.html.twig', $slug), [
            'items' => $items,
        ]);
    }
}
