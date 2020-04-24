<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\Parameters;
use App\Manager\ItemManager;
use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @var ItemProvider
     */
    private $itemProvider;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var Parameters
     */
    private $parametersHelper;

    public function __construct(ItemProvider $itemProvider, ItemManager $itemManager, Parameters $parametersHelper)
    {
        $this->itemProvider = $itemProvider;
        $this->itemManager = $itemManager;
        $this->parametersHelper = $parametersHelper;
    }

    /**
     * @Route("/zaplecze/{page}", name="app_admin_item_list", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     */
    public function list(int $page): Response
    {
        return $this->render('admin/item_list.html.twig', [
            'items' => $this->itemProvider->getAll($page),
        ]);
    }

    /**
     * @Route("/zaplecze/item/edit", name="app_admin_item_edit")
     */
    public function editItem(): Response
    {
        return $this->render('admin/item_edit.html.twig');
    }

    /**
     * @Route("/zaplecze/item/add", name="app_admin_item_add")
     */
    public function addItem(): Response
    {
        return $this->render('admin/item_edit.html.twig');
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
