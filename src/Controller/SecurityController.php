<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Security\LoginThrottle;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/zaplecze/login', name: 'app_admin_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, LoginThrottle $loginThrottle): Response
    {
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute($this->isGranted(User::ROLE_ADMIN) ? 'app_admin_item_list' : 'app_album_index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'lockout_minutes' => (int) ceil($loginThrottle->getRemainingLockoutSeconds((string) $request->getClientIp()) / 60),
        ]);
    }

    #[Route('/zaplecze/logout', name: 'app_admin_logout')]
    public function logout(): never
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
