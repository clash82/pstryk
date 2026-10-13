<?php declare(strict_types=1);

namespace App\Controller\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectHelper extends AbstractController
{
    public function redirectToList(?string $returnPath, string $listRoute): RedirectResponse
    {
        if (null !== $returnPath && $this->isLocalPath($returnPath)) {
            return $this->redirect($returnPath);
        }

        return $this->redirectToRoute($listRoute);
    }

    /**
     * Accepts only paths on the current host (e.g. "/zaplecze/2"), rejects absolute URLs
     * and protocol-relative ones ("//evil.example") to prevent open redirects.
     */
    private function isLocalPath(string $path): bool
    {
        return str_starts_with($path, '/')
            && !str_starts_with($path, '//')
            && !str_contains($path, '\\')
            && 1 !== preg_match('/[\x00-\x1f\x7f]/', $path);
    }
}
