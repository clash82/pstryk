<?php declare(strict_types=1);

namespace App\Controller\Helper;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * This is helper class to be used only on my server. It will be ignored everywhere
 * else and can be removed from any other project.
 */
class CounterHelper
{
    private bool $isEnabled = false;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        if ('test' === $parameterBag->get('kernel.environment')) {
            return;
        }

        // this file is hosted only on my server
        $wrapperFile = \sprintf('%s/../../include/wrappers/counter.php', getcwd());

        if (file_exists($wrapperFile)) {
            /* @noinspection PhpIncludeInspection */
            require_once $wrapperFile;

            $this->isEnabled = true;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function increment(string $counter): void
    {
        if (!$this->isEnabled) {
            return;
        }

        /* @noinspection PhpUndefinedClassInspection */
        \counter::increment($counter);
    }
}
