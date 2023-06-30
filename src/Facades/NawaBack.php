<?php

namespace Nawa\Backend\Facades;

use Illuminate\Support\Facades\Facade;

class NawaBack extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'NawaBack';
    }

}
