<?php declare(strict_types=1);

namespace App\Twig;

use App\Controller\Helper\SeoHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SeoExtension extends AbstractExtension
{
    public function __construct(private readonly SeoHelper $seoHelper)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getSeoButtons', $this->getCode(...)),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getCode(array $style): string
    {
        return $this->seoHelper->getSeoButtons($style);
    }

    public function getName(): string
    {
        return 'matomo_twig_extension';
    }
}
