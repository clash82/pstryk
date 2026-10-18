<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * The "Wyloguj" link placed on the front pages asks (via the "from=front" parameter) to land on the
 * main page after logging out. Logging out from the admin panel still ends on the login page.
 */
#[AsEventListener(event: LogoutEvent::class)]
class LogoutListener
{
    final public const string FROM_FRONT_PAGE = 'front';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(LogoutEvent $event): void
    {
        if (self::FROM_FRONT_PAGE === $event->getRequest()->query->get('from')) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_album_index')));
        }
    }
}
