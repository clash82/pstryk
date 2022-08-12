<?php declare(strict_types=1);

namespace App\Twig;

use App\Controller\Helper\MatomoHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MatomoExtension extends AbstractExtension
{
    private MatomoHelper $matomoHelper;

    public function __construct(MatomoHelper $matomoHelper)
    {
        $this->matomoHelper = $matomoHelper;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getMatomoCode', [$this, 'getCode']),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getCode(int $siteId, bool $useScriptTag = true): string
    {
        return $this->matomoHelper->getCode($siteId, $useScriptTag);
    }

    public function getName(): string
    {
        return 'matomo_twig_extension';
    }
}
