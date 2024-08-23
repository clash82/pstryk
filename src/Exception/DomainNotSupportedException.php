<?php declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class DomainNotSupportedException extends Exception
{
    public function __construct(string $domain, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(\sprintf('Domain `%s` is not supported', $domain), $code, $previous);
    }
}
