<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\CounterHelper;
use App\Controller\Helper\DomainHelper;
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

    /** @var DomainHelper */
    private $domainHelper;

    /** @var CounterHelper */
    private $counterHelper;

    public function __construct(
        ItemProvider $itemProvider,
        AlbumProvider $albumProvider,
        DomainHelper $domainHelper,
        CounterHelper $counterHelper
    ) {
        $this->itemProvider = $itemProvider;
        $this->albumProvider = $albumProvider;
        $this->domainHelper = $domainHelper;
        $this->counterHelper = $counterHelper;
    }

    /**
     * @Route("/{page}", name="app_album_index", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     */
    public function index(int $page): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        $this->counterHelper->increment($album->getSlug());

        /* @noinspection PhpUnhandledExceptionInspection */
        $items = $this->itemProvider->getAllPaginated(
            $album->getSlug(),
            $page,
            $this->albumProvider->getBySlug($album->getSlug())->getPaginationLimit()
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render(sprintf('album/%s/index.html.twig', $album->getSlug()), [
            'items' => $items,
            'album' => $this->albumProvider->getBySlug($album->getSlug()),
        ]);
    }

    /**
     * @Route("/{itemSlug}", requirements={"itemSlug" = "^((?!feed|zaplecze|robots\.txt).)*$"}, name="app_album_item")
     */
    public function item(string $itemSlug): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        try {
            $item = $this->itemProvider->getBySlug($album->getSlug(), $itemSlug);
        } catch (RecordNotFoundException $e) {
            return $this->redirectToRoute('app_album_index');
        }

        $this->counterHelper->increment($album->getSlug());

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render(sprintf('album/%s/item.html.twig', $album->getSlug()), [
            'item' => $item,
            'next_item' => $this->itemProvider->getNext($item),
            'prev_item' => $this->itemProvider->getPrevious($item),
            'album' => $this->albumProvider->getBySlug($album->getSlug()),
        ]);
    }
}
