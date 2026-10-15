<?php declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\ParametersHelper;
use App\Controller\Helper\RedirectHelper;
use App\Entity\Item;
use App\Form\ImageType;
use App\Form\ItemType;
use App\Manager\AdminSettingsManager;
use App\Manager\ItemManager;
use App\Manager\PendingUploadManager;
use App\Provider\AdminSettingsProvider;
use App\Provider\AlbumProvider;
use App\Provider\ItemProvider;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminController extends AbstractController
{
    public function __construct(private readonly ItemProvider $itemProvider, private readonly ItemManager $itemManager, private readonly AlbumProvider $albumProvider, private readonly ParametersHelper $parametersHelper, private readonly RedirectHelper $redirectHelper, private readonly AdminSettingsProvider $adminSettingsProvider, private readonly AdminSettingsManager $adminSettingsManager, private readonly PendingUploadManager $pendingUploadManager, private readonly ValidatorInterface $validator)
    {
    }

    #[Route('/zaplecze/{page}', name: 'app_admin_item_list', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
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

    #[Route('/zaplecze/item-filter-options', name: 'app_admin_item_filter_options', condition: 'request.isXmlHttpRequest()')]
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

    #[Route('/zaplecze/item/edit/{itemId}', name: 'app_admin_item_edit', requirements: ['itemId' => '\d+'])]
    public function editItem(Request $request, int $itemId): Response
    {
        $item = $this->itemProvider->getById($itemId);
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && ($form->isSubmitted() && $form->isValid())) {
            $this->itemManager->update($item);

            return $this->redirectHelper->redirectToList($request->query->get('return'), 'app_admin_item_list');
        }

        return $this->render('admin/parts/form.html.twig', [
            'title' => 'Edytuj zestaw zdjęć',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/zaplecze/item/add', name: 'app_admin_item_add')]
    public function addItem(Request $request): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item, [
            'creation_type' => 'create',
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && ($form->isSubmitted() && $form->isValid())) {
            $this->itemManager->update($item);

            return $this->redirectHelper->redirectToList($request->query->get('return'), 'app_admin_item_list');
        }

        return $this->render('admin/parts/form.html.twig', [
            'title' => 'Dodaj zestaw zdjęć',
            'form' => $form->createView(),
        ]);
    }

    /**
     * Receives a single image from the drop zone. Files are sent one per request, so the number of images
     * in the item is not limited by max_file_uploads, post_max_size or max_input_time. The item form
     * refers to the stored file by the returned token.
     */
    #[Route('/zaplecze/item/upload-image', name: 'app_admin_item_upload_image', methods: ['POST'], condition: 'request.isXmlHttpRequest()')]
    public function uploadImage(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            // typically means that the file was bigger than post_max_size and PHP dropped the whole request
            return new JsonResponse([
                'errorCode' => 1,
                'errorDescription' => 'Nie otrzymano pliku',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$file->isValid()) {
            return new JsonResponse([
                'errorCode' => 1,
                'errorDescription' => $file->getErrorMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $violations = $this->validator->validate($file, ImageType::createFileConstraint());

        if (\count($violations) > 0) {
            return new JsonResponse([
                'errorCode' => 1,
                'errorDescription' => $violations->get(0)->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $album = (string) $request->request->get('album');

        if (!$this->albumProvider->slugExists($album)) {
            return new JsonResponse([
                'errorCode' => 1,
                'errorDescription' => 'Wybierz album, zanim dodasz zdjęcia',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $token = $this->pendingUploadManager->store($file);
            // converting right away keeps saving the item fast, no matter how many images it has
            $this->pendingUploadManager->process($token, $album);
        } catch (Exception $exception) {
            // most likely a damaged image, so repeating the request would not help
            return new JsonResponse([
                'errorCode' => 2,
                'errorDescription' => 'Nie udało się przetworzyć obrazu: '.$exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'errorCode' => 0,
            'token' => $token,
        ]);
    }

    /**
     * Converts an already uploaded image again, for another album (the album defines sizes and watermark).
     */
    #[Route('/zaplecze/item/process-image', name: 'app_admin_item_process_image', methods: ['POST'], condition: 'request.isXmlHttpRequest()')]
    public function processImage(Request $request): JsonResponse
    {
        try {
            $this->pendingUploadManager->process(
                (string) $request->request->get('token'),
                (string) $request->request->get('album')
            );
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse([
                'errorCode' => 1,
                'errorDescription' => $exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            return new JsonResponse([
                'errorCode' => 2,
                'errorDescription' => 'Nie udało się przetworzyć obrazu: '.$exception->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'errorCode' => 0,
        ]);
    }

    #[Route('/zaplecze/item/delete', name: 'app_admin_item_delete', condition: 'request.isXmlHttpRequest()')]
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
