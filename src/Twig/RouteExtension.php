<?php declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouteExtension extends AbstractExtension
{
    protected array $attributes = [
        'page' => 1,
    ];

    public function __construct(RequestStack $requestStack)
    {
        /** @var Request $request */
        $request = $requestStack->getCurrentRequest();

        if ($request instanceof Request) {
            $this->attributes = (array) $request->attributes->get('_route_params');
        }
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getRoute', [$this, 'getRoute']),
            new TwigFunction('getRouteWithPagination', [$this, 'getRouteWithPagination']),
        ];
    }

    public function getRoute(?string $itemSlug): string
    {
        return $this->generateRoute($itemSlug);
    }

    public function getRouteWithPagination(?string $itemSlug): string
    {
        $route = $this->generateRoute($itemSlug);

        if (isset($this->attributes['page']) && (int) $this->attributes['page'] > 1) {
            return sprintf('%s/%d', $route, $this->attributes['page']);
        }

        return $route;
    }

    public function getName(): string
    {
        return 'route_twig_extension';
    }

    private function generateRoute(?string $itemSlug): string
    {
        if ('' === $itemSlug) {
            $itemSlug = $this->attributes['itemSlug'] ?? null;
        }

        return sprintf(
            'http%s://%s%s',
            !isset($_SERVER['HTTPS']) || 'on' !== $_SERVER['HTTPS'] ? '' : 's',
            $_SERVER['SERVER_NAME'],
            empty($itemSlug) ? '' : sprintf('/%s', $itemSlug)
        );
    }
}
