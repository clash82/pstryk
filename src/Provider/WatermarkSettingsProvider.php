<?php

declare(strict_types=1);

namespace App\Provider;

use App\Exception\WatermarkSettingsNotFoundException;
use App\Value\Album;
use App\Value\Watermark;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WatermarkSettingsProvider
{
    /** @var ParameterBagInterface */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function get(Album $album): Watermark
    {
        $albums = $this->parameterBag->get('app')['albums'];

        foreach ($albums as $slug => $settings) {
            if ($slug === $album->getSlug()) {
                /* @noinspection PhpUnhandledExceptionInspection */
                return new Watermark($settings['watermark']);
            }
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        throw new WatermarkSettingsNotFoundException();
    }
}
