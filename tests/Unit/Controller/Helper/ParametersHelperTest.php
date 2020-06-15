<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Helper;

use App\Controller\Helper\ParametersHelper;
use PHPUnit\Framework\TestCase;

class ParametersHelperTest extends TestCase
{
    public function testResolveParameters()
    {
        $parametersHelper = new ParametersHelper();

        $expected = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $this->assertEquals($expected, $parametersHelper->resolveParameters([
            'field1', 'field2',
        ], $expected));
    }
}
