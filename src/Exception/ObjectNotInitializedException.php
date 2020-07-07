<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class ObjectNotInitializedException extends Exception
{
    public function __construct(string $key, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf('Class object `%s` not initialized', $key), $code, $previous);
    }
}
