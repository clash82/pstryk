<?php declare(strict_types=1);

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Do not rely on auto-detection (it looks for composer.json), so the project
     * root is correct even when composer.json is not deployed to the server.
     */
    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    protected function build(ContainerBuilder $container): void
    {
        // makes the "app:" configuration root (config/packages/app.yaml) available
        $container->registerExtension(new AppExtension());
    }
}
