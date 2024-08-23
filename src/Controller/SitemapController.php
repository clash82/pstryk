<?php declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\DomainHelper;
use App\Provider\ItemProvider;
use App\Value\Album;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    public function __construct(private readonly ItemProvider $itemProvider, private readonly DomainHelper $domainHelper)
    {
    }

    /**
     * @Route("/sitemap", name="app_sitemap_index")
     */
    public function index(): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        if (!$album->getSitemap()) {
            return $this->redirectToRoute('app_album_index');
        }

        $items = $this->getAllItems($album);

        $history = [];
        foreach ($items as $item) {
            $year = $item->getDate()->format('Y');
            $history[$year][] = $item;
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render(\sprintf('album/%s/sitemap.html.twig', $album->getSlug()), [
            'history' => $history,
            'album' => $album,
        ]);
    }

    /**
     * @Route("/sitemap.xml", name="app_sitemap_xml_index", defaults={"_format"="xml"})
     */
    public function indexXml(): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        if (!$album->getSitemap()) {
            return $this->redirectToRoute('app_album_index');
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->render('sitemap/index.html.twig', [
            'items' => $this->getAllItems($album),
        ]);
    }

    private function getAllItems(Album $album): PaginationInterface
    {
        return $this->itemProvider->getAllPaginated(
            $album->getSlug(),
            1,
            \PHP_INT_MAX
        );
    }
}
