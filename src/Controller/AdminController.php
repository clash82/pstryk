<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\ParametersHelper;
use App\Controller\Helper\RedirectHelper;
use App\Entity\Item;
use App\Form\ItemType;
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
     * @var ParametersHelper
     */
    private $parametersHelper;

    /**
     * @var RedirectHelper
     */
    private $redirectHelper;

    public function __construct(
        ItemProvider $itemProvider,
        ItemManager $itemManager,
        ParametersHelper $parametersHelper,
        RedirectHelper $redirectHelper
    ) {
        $this->itemProvider = $itemProvider;
        $this->itemManager = $itemManager;
        $this->parametersHelper = $parametersHelper;
        $this->redirectHelper = $redirectHelper;
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
     * @Route("/zaplecze/item/edit/{itemId}", name="app_admin_item_edit", requirements={"itemId" = "\d+"})
     */
    public function editItem(Request $request, int $itemId): Response
    {
        $item = $this->itemProvider->getById($itemId);
        $form = $this->createForm(ItemType::class, $item);

        if ($request->isMethod('POST')) {
            $form->submit($request->request->get($form->getName()));

            if ($form->isSubmitted() && $form->isValid()) {
                $this->itemManager->update($item);

                $this->redirectHelper->redirectToList($request->query->get('return'), 'app_admin_item_list');
            }
        }

        return $this->render('admin/parts/form.html.twig', [
            'title' => 'Edytuj zestaw',
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

        if ($request->isMethod('POST')) {
            $form->submit($request->request->get($form->getName()));

            if ($form->isSubmitted() && $form->isValid()) {
                $this->itemManager->update($item);

                $this->redirectHelper->redirectToList($request->query->get('return'), 'app_admin_item_list');
            }
        }

        return $this->render('admin/parts/form.html.twig', [
            'title' => 'Dodaj zestaw',
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
