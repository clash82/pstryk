<?php declare(strict_types=1);

namespace App\Value;

use App\Exception\ArrayKeyNotExistsException;

class Domains
{
    private array $domains = [];

    public function __construct(array $settings = [])
    {
        if (!isset($settings['domains'])) {
            /* @noinspection PhpUnhandledExceptionInspection */
            throw new ArrayKeyNotExistsException('domains');
        }
        $this->domains = $settings['domains'];
    }

    public function getAll(): array
    {
        return $this->domains;
    }
}
