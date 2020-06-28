<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\DomainHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RobotsController extends AbstractController
{
    /** @var DomainHelper */
    private $domainHelper;

    /** @var Packages */
    private $packages;

    public function __construct(
        DomainHelper $domainHelper,
        Packages $packages
    ) {
        $this->domainHelper = $domainHelper;
        $this->packages = $packages;
    }

    /**
     * @Route("/robots.txt", name="app_robots_index", defaults={"_format"="txt"})
     */
    public function index(): Response
    {
        /* @noinspection PhpUnhandledExceptionInspection */
        $album = $this->domainHelper->getCurrentAlbum();

        /** @var string $robots */
        $robots = file_get_contents(sprintf(
            '%s/%s',
            getcwd(),
            $this->packages->getUrl(sprintf(
                'assets/robots/%s/robots.txt',
                $album->getSlug()
            ))
        ));

        $response = new Response();
        $response->setContent($robots);

        return $response;
    }
}
