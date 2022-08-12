<?php declare(strict_types=1);

namespace App\Controller\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParametersHelper extends AbstractController
{
    public function resolveParameters(array $required = [], array $available = []): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired($required);

        return $resolver->resolve($available);
    }
}
