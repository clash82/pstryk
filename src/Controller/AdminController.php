<?php

declare(strict_types=1);

namespace App\Controller;

use App\Provider\ItemProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private $itemProvider;

    public function __construct(ItemProvider $itemProvider)
    {
        $this->itemProvider = $itemProvider;
    }

    /**
     * @Route("/zaplecze/{page}", name="app_admin_item_list", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     */
    public function list(int $page)
    {
        return $this->render('admin/item_list.html.twig', [
            'items' => $this->itemProvider->getAll($page),
        ]);
    }

    /**
     * @Route("/zaplecze/item/edit", name="app_admin_item_edit")
     */
    public function editItem()
    {
        return $this->render('admin/item_edit.html.twig');
    }

    /**
     * @Route("/zaplecze/item/add", name="app_admin_item_add")
     */
    public function addItem()
    {
        return $this->render('admin/item_edit.html.twig');
    }
}
