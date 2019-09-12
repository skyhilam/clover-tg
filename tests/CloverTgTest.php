<?php

namespace Clover\CloverTg\Tests;

use Clover\CloverTg\Facades\CloverTg;
use Clover\CloverTg\ServiceProvider;
use Orchestra\Testbench\TestCase;

class CloverTgTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'clover-tg' => CloverTg::class,
        ];
    }

    public function testExample()
    {
        $this->assertEquals(1, 1);
    }
}
