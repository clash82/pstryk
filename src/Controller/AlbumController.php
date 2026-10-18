<?php declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\CounterHelper;
use App\Controller\Helper\DomainHelper;
use App\Exception\RecordNotFoundException;
use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{
    public function __construct(private readonly ItemProvider $itemProvider, private readonly AlbumProvider $albumProvider, private readonly DomainHelper $domainHelper, private readonly CounterHelper $counterHelper)
    {
    }

    #[Route('/{page}', name: 'app_album_index', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function index(int $page): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        $this->counterHelper->increment($album->getSlug());

        $activeOnly = $this->isActiveOnly();

        /* @noinspection PhpUnhandledExceptionInspection */
        $items = $this->itemProvider->getAllPaginated(
            $album->getSlug(),
            $page,
            $this->albumProvider->getBySlug($album->getSlug())->getPaginationLimit(),
            activeOnly: $activeOnly
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render(\sprintf('album/%s/index.html.twig', $album->getSlug()), [
            'items' => $items,
            'show_hidden' => !$activeOnly,
            'album' => $this->albumProvider->getBySlug($album->getSlug()),
        ]);
    }

    #[Route('/{itemSlug}', name: 'app_album_item', requirements: ['itemSlug' => '^((?!feed|sitemap|zaplecze|robots\.txt|sitemap\.xml).)*$'])]
    public function item(string $itemSlug): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        $activeOnly = $this->isActiveOnly();

        try {
            $item = $this->itemProvider->getBySlug($album->getSlug(), $itemSlug, $activeOnly);
        } catch (RecordNotFoundException) {
            return $this->redirectToRoute('app_album_index');
        }

        $this->counterHelper->increment($album->getSlug());

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render(\sprintf('album/%s/item.html.twig', $album->getSlug()), [
            'item' => $item,
            'show_hidden' => !$activeOnly,
            'next_item' => $this->itemProvider->getNext($item, $activeOnly),
            'prev_item' => $this->itemProvider->getPrevious($item, $activeOnly),
            'album' => $this->albumProvider->getBySlug($album->getSlug()),
        ]);
    }

    /**
     * Every logged in account (administrators included) also sees private (inactive) items and images on the front pages.
     */
    private function isActiveOnly(): bool
    {
        return !$this->isGranted('ROLE_USER');
    }
}
