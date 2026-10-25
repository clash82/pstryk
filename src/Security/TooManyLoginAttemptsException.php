<?php declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class TooManyLoginAttemptsException extends CustomUserMessageAuthenticationException
{
    public function __construct(int $remainingSeconds)
    {
        parent::__construct(
            'Zbyt wiele nieudanych prób logowania. Logowanie jest chwilowo niedostępne, spróbuj ponownie za %minutes% min.',
            ['%minutes%' => (int) ceil($remainingSeconds / 60)]
        );
    }
}
