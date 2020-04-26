<?php

declare(strict_types=1);

namespace App\Provider;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AlbumProvider
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getAll(): array
    {
        return $this->parameterBag->get('app')['albums'];
    }
}
