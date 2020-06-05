<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\ParametersHelper;
use App\Controller\Helper\RedirectHelper;
use App\Entity\Item;
use App\Form\ItemType;
use App\Manager\ItemManager;
use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /** @var ItemProvider */
    private $itemProvider;

    /** @var ItemManager */
    private $itemManager;

    /** @var AlbumProvider */
    private $albumProvider;

    /** @var ParametersHelper */
    private $parametersHelper;

    /** @var RedirectHelper */
    private $redirectHelper;

    public function __construct(
        ItemProvider $itemProvider,
        ItemManager $itemManager,
        AlbumProvider $albumProvider,
        ParametersHelper $parametersHelper,
        RedirectHelper $redirectHelper
    ) {
        $this->itemProvider = $itemProvider;
        $this->itemManager = $itemManager;
        $this->albumProvider = $albumProvider;
        $this->parametersHelper = $parametersHelper;
        $this->redirectHelper = $redirectHelper;
    }

    /**
     * @Route("/zaplecze/{page}", name="app_admin_item_list", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     */
    public function list(Request $request, int $page): Response
    {
        $itemsPerPage = (int) $request->cookies->get(
            'item_filter_options_items_per_page',
            ItemProvider::DEFAULT_PAGE_LIMIT
        );
        $itemsSort = $request->cookies->get(
            'item_filter_options_items_sort',
            ItemProvider::DEFAULT_SORT_COLUMN
        );
        $itemsSortDirection = $request->cookies->get(
            'item_filter_options_items_sort_direction',
            ItemProvider::DEFAULT_SORT_DIRECTION
        );
        $album = $request->cookies->get(
            'item_filter_options_album',
            ''
        );

        return $this->render('admin/item_list.html.twig', [
            'items' => $this->itemProvider->getAllPaginated(
                $album,
                $page,
                $itemsPerPage,
                $itemsSort,
                $itemsSortDirection
            ),
            'albums' => $this->albumProvider->getAll(),
             'filter_options' => [
                'items_per_page' => $itemsPerPage,
                'items_sort' => $itemsSort,
                'items_sort_direction' => $itemsSortDirection,
                'album' => $album,
            ],
        ]);
    }

    /**
     * @Route("/zaplecze/item-filter-options", name="app_admin_item_filter_options", condition="request.isXmlHttpRequest()")
     */
    public function filterOptions(Request $request): JsonResponse
    {
        $parameters = $this->parametersHelper->resolveParameters([
            'itemsPerPage',
            'itemsSort',
            'itemsSortDirection',
            'album',
        ], $request->request->all());

        $jsonResponse = new JsonResponse([
            'errorCode' => '0',
        ]);

        $jsonResponse->headers->setCookie(
            new Cookie('item_filter_options_items_per_page', $parameters['itemsPerPage'])
        );
        $jsonResponse->headers->setCookie(
            new Cookie('item_filter_options_items_sort', $parameters['itemsSort'])
        );
        $jsonResponse->headers->setCookie(
            new Cookie('item_filter_options_items_sort_direction', $parameters['itemsSortDirection'])
        );
        $jsonResponse->headers->setCookie(
            new Cookie('item_filter_options_album', $parameters['album'])
        );

        return $jsonResponse;
    }

    /**
     * @Route("/zaplecze/item/edit/{itemId}", name="app_admin_item_edit", requirements={"itemId" = "\d+"})
     */
    public function editItem(Request $request, int $itemId): Response
    {
        $item = $this->itemProvider->getById($itemId);
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $this->itemManager->update($item);

                $this->redirectHelper->redirectToList($request->query->get('return'), 'app_admin_item_list');
            }
        }

        return $this->render('admin/parts/form.html.twig', [
            'title' => 'Edytuj zestaw zdjęć',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/zaplecze/item/add", name="app_admin_item_add")
     */
    public function addItem(Request $request): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item, [
            'creation_type' => 'create',
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $this->itemManager->update($item);

                $this->redirectHelper->redirectToList($request->query->get('return'), 'app_admin_item_list');
            }
        }

        return $this->render('admin/parts/form.html.twig', [
            'title' => 'Dodaj zestaw zdjęć',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/zaplecze/item/delete", name="app_admin_item_delete", condition="request.isXmlHttpRequest()")
     */
    public function deleteItem(Request $request): JsonResponse
    {
        $parameters = $this->parametersHelper->resolveParameters([
            'itemId',
        ], $request->request->all());

        $errorCode = 0;
        $errorDescription = null;

        try {
            $this->itemManager->deleteById((int) $parameters['itemId']);
        } catch (\Exception $e) {
            $errorCode = 1;
            $errorDescription = $e->getMessage();
        }

        $jsonResponse = new JsonResponse([
            'errorCode' => $errorCode,
            'errorDescription' => $errorDescription,
        ]);

        return $jsonResponse;
    }
}
