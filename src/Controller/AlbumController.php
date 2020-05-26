<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\RecordNotFoundException;
use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    /** @var ItemProvider */
    private $itemProvider;

    /** @var AlbumProvider */
    private $albumProvider;

    public function __construct(ItemProvider $itemProvider, AlbumProvider $albumProvider)
    {
        $this->itemProvider = $itemProvider;
        $this->albumProvider = $albumProvider;
    }

    /**
     * @Route("/", name="app_album_index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('app_album_list', [
            'albumSlug' => 'stalker',
        ]);
    }

    /**
     * @Route("/{albumSlug}/{page}", name="app_album_list", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     */
    public function list(string $albumSlug, int $page): Response
    {
        if (!$this->albumProvider->slugExists($albumSlug)) {
            return $this->redirectToRoute('app_album_index');
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        $items = $this->itemProvider->getAllPaginated(
            $albumSlug,
            $page,
            $this->albumProvider->getBySlug($albumSlug)->getPaginationLimit()
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render(sprintf('album/%s/index.html.twig', $albumSlug), [
            'items' => $items,
            'album' => $this->albumProvider->getBySlug($albumSlug),
        ]);
    }

    /**
     * @Route("/{albumSlug}/{itemSlug}", requirements={
     *     "albumSlug" = "^((?!zaplecze).)*$",
     *     "itemSlug" = "^((?!feed).)*$"
     * }, name="app_album_item")
     */
    public function item(string $albumSlug, string $itemSlug): Response
    {
        if (!$this->albumProvider->slugExists($albumSlug)) {
            return $this->redirectToRoute('app_album_index');
        }

        try {
            $item = $this->itemProvider->getBySlug($albumSlug, $itemSlug);
        } catch (RecordNotFoundException $e) {
            return $this->redirectToRoute('app_album_index');
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render(sprintf('album/%s/item.html.twig', $albumSlug), [
            'item' => $item,
            'album' => $this->albumProvider->getBySlug($albumSlug),
        ]);
    }
}
