<?php declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\ParametersHelper;
use App\Controller\Helper\RedirectHelper;
use App\Entity\Item;
use App\Form\ItemType;
use App\Manager\AdminSettingsManager;
use App\Manager\ItemManager;
use App\Provider\AdminSettingsProvider;
use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function __construct(private ItemProvider $itemProvider, private ItemManager $itemManager, private AlbumProvider $albumProvider, private ParametersHelper $parametersHelper, private RedirectHelper $redirectHelper, private AdminSettingsProvider $adminSettingsProvider, private AdminSettingsManager $adminSettingsManager)
    {
    }

    /**
     * @Route("/zaplecze/{page}", name="app_admin_item_list", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     */
    public function list(int $page): Response
    {
        $itemsPerPage = $this->adminSettingsProvider->getItemsPerPage();
        $itemsSort = $this->adminSettingsProvider->getItemsSort();
        $itemsSortDirection = $this->adminSettingsProvider->getItemsSortDirection();
        $album = $this->adminSettingsProvider->getAlbum();

        return $this->render('admin/item_list.html.twig', [
            'items' => $this->itemProvider->getAllPaginated(
                $album,
                $page,
                $itemsPerPage,
                $itemsSort,
                $itemsSortDirection,
                false
            ),
            'albums' => $this->albumProvider->getAll(),
            'filter_options' => [
                AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_PER_PAGE => $itemsPerPage,
                AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_SORT => $itemsSort,
                AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_SORT_DIRECTION => $itemsSortDirection,
                AdminSettingsProvider::ITEM_FILTER_OPTIONS_ALBUM => $album,
            ],
        ]);
    }

    /**
     * @Route("/zaplecze/item-filter-options", name="app_admin_item_filter_options", condition="request.isXmlHttpRequest()")
     */
    public function filterOptions(Request $request): JsonResponse
    {
        $parameters = $this->parametersHelper->resolveParameters([
            AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_PER_PAGE,
            AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_SORT,
            AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_SORT_DIRECTION,
            AdminSettingsProvider::ITEM_FILTER_OPTIONS_ALBUM,
        ], $request->request->all());

        $jsonResponse = new JsonResponse([
            'errorCode' => 0,
        ]);

        return $this->adminSettingsManager
            ->setJsonResponse($jsonResponse)
            ->setItemsPerPage((int) $parameters[AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_PER_PAGE])
            ->setItemsSort($parameters[AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_SORT])
            ->setItemsSortDirection($parameters[AdminSettingsProvider::ITEM_FILTER_OPTIONS_ITEMS_SORT_DIRECTION])
            ->setAlbum($parameters[AdminSettingsProvider::ITEM_FILTER_OPTIONS_ALBUM])
            ->getJsonResponse();
    }

    /**
     * @Route("/zaplecze/item/edit/{itemId}", name="app_admin_item_edit", requirements={"itemId" = "\d+"})
     */
    public function editItem(Request $request, int $itemId): Response
    {
        $item = $this->itemProvider->getById($itemId);
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && ($form->isSubmitted() && $form->isValid())) {
            $this->itemManager->update($item);
            $this->redirectHelper->redirectToList($request->query->get('return'), 'app_admin_item_list');
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

        if ($request->isMethod('POST') && ($form->isSubmitted() && $form->isValid())) {
            $this->itemManager->update($item);
            $this->redirectHelper->redirectToList($request->query->get('return'), 'app_admin_item_list');
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
        } catch (Exception $e) {
            $errorCode = 1;
            $errorDescription = $e->getMessage();
        }

        return new JsonResponse([
            'errorCode' => $errorCode,
            'errorDescription' => $errorDescription,
        ]);
    }
}
