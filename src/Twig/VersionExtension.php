<?php declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\HttpKernel\Kernel as KernelAlias;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VersionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getPhpVersion', $this->getPhpVersion(...)),
            new TwigFunction('getSymfonyVersion', $this->getSymfonyVersion(...)),
        ];
    }

    public function getPhpVersion(): string
    {
        return \PHP_VERSION;
    }

    public function getSymfonyVersion(): string
    {
        return KernelAlias::VERSION;
    }
}
