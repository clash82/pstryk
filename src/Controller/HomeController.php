<?php

declare(strict_types=1);

namespace App\Controller;

use App\Provider\AlbumProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /** @var AlbumProvider */
    private $albumProvider;

    public function __construct(AlbumProvider $albumProvider)
    {
        $this->albumProvider = $albumProvider;
    }

    /**
     * @Route("/", name="app_home_index")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'albums' => $this->albumProvider->getAll(),
        ]);
    }
}
