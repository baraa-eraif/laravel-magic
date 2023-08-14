<?php

namespace LaravelMagic\Backend\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasFilters
{

    
    
    public function scopeSearch(Builder $query, Request $request)
    {
       return $query;
    }

}
