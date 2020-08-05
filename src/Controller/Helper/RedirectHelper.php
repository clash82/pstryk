<?php

declare(strict_types=1);

namespace App\Controller\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectHelper extends AbstractController
{
    public function redirectToList(?string $returnPath, string $listRoute): RedirectResponse
    {
        if (!empty($returnPath)) {
            $this->redirect($returnPath)->sendHeaders();

            exit;
        }

        return $this->redirectToRoute($listRoute);
    }
}
