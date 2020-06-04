<?php

declare(strict_types=1);

namespace App\Provider;

use App\Exception\TagsSettingsNotFoundException;
use App\Value\Album;
use App\Value\Tags;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TagsSettingsProvider
{
    /** @var ParameterBagInterface */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function get(Album $album): Tags
    {
        $albums = $this->parameterBag->get('app')['albums'];

        foreach ($albums as $slug => $settings) {
            if ($slug === $album->getSlug()) {
                /* @noinspection PhpUnhandledExceptionInspection */
                return new Tags($settings['tags']);
            }
        }

        /* @noinspection PhpUnhandledExceptionInspection */
        throw new TagsSettingsNotFoundException();
    }
}
