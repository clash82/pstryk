<?php declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ControllerActionExtension extends AbstractExtension
{
    public function __construct(protected RequestStack $requestStack)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getControllerName', $this->getControllerName(...)),
            new TwigFunction('getActionName', $this->getActionName(...)),
        ];
    }

    public function getControllerName(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request instanceof Request) {
            $pattern = "#Controller\\\([a-zA-Z]*)Controller#";
            $matches = [];
            preg_match($pattern, (string) $request->get('_controller'), $matches);

            return strtolower($matches[1]);
        }

        return '';
    }

    public function getActionName(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request instanceof Request) {
            $pattern = '#::([a-zA-Z]*)#';
            $matches = [];
            preg_match($pattern, (string) $request->get('_controller'), $matches);

            return $matches[1];
        }

        return '';
    }

    public function getName(): string
    {
        return 'controller_action_twig_extension';
    }
}
