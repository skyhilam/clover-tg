<?php

namespace Clover\CloverTg\Facades;

use Illuminate\Support\Facades\Facade;

class CloverTg extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'clover-tg';
    }
}
