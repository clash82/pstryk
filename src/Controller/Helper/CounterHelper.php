<?php

declare(strict_types=1);

namespace App\Controller\Helper;

/**
 * This is helper class to be used only on my server. It will be ignored everywhere
 * else and can be removed from any other project.
 */
class CounterHelper
{
    /** @var bool */
    private $isEnabled = false;

    public function __construct()
    {
        // this file is hosted only on my server
        $counterFile = sprintf('%s/../../include/wrappers/counter.php', getcwd());

        if (file_exists($counterFile)) {
            /* @noinspection PhpIncludeInspection */
            require_once $counterFile;

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
        /* @phan-suppress-next-line PhanUndeclaredClassMethod */
        \counter::increment($counter);
    }
}
