<?php

namespace LaravelMagic\Models;


use \Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \LaravelMagic\Traits\HasFilters;

class BaseModel extends Model
{
    use HasFilters;

    protected $imageable = [];



}
