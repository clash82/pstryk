<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouteExtension extends AbstractExtension
{
    /** @var array */
    protected $attributes = [];

    public function __construct(RequestStack $requestStack)
    {
        /** @var Request $request */
        $request = $requestStack->getCurrentRequest();
        $this->attributes = $request->attributes->get('_route_params');
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getRoute', [$this, 'getRoute']),
            new TwigFunction('getRouteWithPagination', [$this, 'getRouteWithPagination']),
        ];
    }

    public function getRoute(?string $albumSlug, ?string $itemSlug): string
    {
        return $this->generateRoute($albumSlug, $itemSlug);
    }

    public function getRouteWithPagination(?string $albumSlug, ?string $itemSlug): string
    {
        $route = $this->generateRoute($albumSlug, $itemSlug);

        if (isset($this->attributes['page']) && (int) $this->attributes['page'] > 1) {
            return sprintf('%s/%d', $route, $this->attributes['page']);
        }

        return $route;
    }

    public function getName(): string
    {
        return 'route_twig_extension';
    }

    private function generateRoute(?string $albumSlug, ?string $itemSlug): string
    {
        if (empty($albumSlug) || empty($itemSlug)) {
            if ('' === $albumSlug) {
                $albumSlug = $this->attributes['albumSlug'] ?? '';
            }

            if ('' === $itemSlug) {
                $itemSlug = $this->attributes['itemSlug'] ?? null;
            }
        }

        return sprintf(
            'http%s://%s%s%s',
            !isset($_SERVER['HTTPS']) || 'on' !== $_SERVER['HTTPS'] ? '' : 's',
            $_SERVER['SERVER_NAME'],
            empty($albumSlug) ? '' : sprintf('/%s', $albumSlug),
            empty($itemSlug) ? '' : sprintf('/%s', $itemSlug)
        );
    }
}
