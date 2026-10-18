<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * Logged in regular users (ROLE_USER) that try to open the admin panel are sent to the front page
 * instead of seeing an error. In any other case the default "403 Forbidden" response is used.
 */
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        if (
            $this->authorizationChecker->isGranted(User::ROLE_USER)
            && !$this->authorizationChecker->isGranted(User::ROLE_ADMIN)
        ) {
            return new RedirectResponse($this->urlGenerator->generate('app_album_index'));
        }

        // null means: let the framework answer with the standard 403 page
        return null;
    }
}
