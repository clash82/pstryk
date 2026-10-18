<?php declare(strict_types=1);

namespace App\Controller;

use App\Controller\Helper\ParametersHelper;
use App\Entity\User;
use App\Exception\RecordNotFoundException;
use App\Form\UserType;
use App\Manager\UserManager;
use App\Provider\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly UserManager $userManager,
        private readonly ParametersHelper $parametersHelper
    ) {
    }

    #[Route('/zaplecze/users/{page}', name: 'app_admin_user_list', requirements: ['page' => '\d+'], defaults: ['page' => 1])]
    public function list(int $page): Response
    {
        $currentUser = $this->getUser();

        return $this->render('admin/user_list.html.twig', [
            'users' => $this->userProvider->getAllPaginated($page),
            'current_user_id' => $currentUser instanceof User ? $currentUser->getId() : null,
        ]);
    }

    #[Route('/zaplecze/user/add', name: 'app_admin_user_add')]
    public function addUser(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'creation_type' => 'create',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->save($user, $this->getPlainPassword($form));

            return $this->redirectToRoute('app_admin_user_list');
        }

        return $this->render('admin/user_form.html.twig', [
            'title' => 'Dodaj użytkownika',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/zaplecze/user/edit/{userId}', name: 'app_admin_user_edit', requirements: ['userId' => '\d+'])]
    public function editUser(Request $request, int $userId): Response
    {
        try {
            $user = $this->userProvider->getById($userId);
        } catch (RecordNotFoundException) {
            throw $this->createNotFoundException();
        }

        // the form modifies the entity while handling the request, so the previous state is remembered now
        $wasAdmin = $user->isAdmin();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $wasAdmin && !$user->isAdmin() && $this->userProvider->countAdmins() <= 1) {
            $form->get('roles')->addError(new FormError('Nie możesz odebrać uprawnień ostatniemu administratorowi'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->save($user, $this->getPlainPassword($form));

            return $this->redirectToRoute('app_admin_user_list');
        }

        return $this->render('admin/user_form.html.twig', [
            'title' => 'Edytuj użytkownika',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/zaplecze/user/delete', name: 'app_admin_user_delete', condition: 'request.isXmlHttpRequest()')]
    public function deleteUser(Request $request): JsonResponse
    {
        $parameters = $this->parametersHelper->resolveParameters([
            'userId',
        ], $request->request->all());

        $errorCode = 0;
        $errorDescription = null;

        try {
            $currentUser = $this->getUser();

            $this->userManager->deleteById(
                (int) $parameters['userId'],
                $currentUser instanceof User ? $currentUser : null
            );
        } catch (\Exception $exception) {
            $errorCode = 1;
            $errorDescription = $exception->getMessage();
        }

        return new JsonResponse([
            'errorCode' => $errorCode,
            'errorDescription' => $errorDescription,
        ]);
    }

    private function getPlainPassword(FormInterface $form): ?string
    {
        $plainPassword = $form->get('plainPassword')->getData();

        return \is_string($plainPassword) ? $plainPassword : null;
    }
}
