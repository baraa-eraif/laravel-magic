<?php

namespace LaravelMagic\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelMagic extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'LaravelMagic';
    }

}
