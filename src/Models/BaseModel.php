<?php

namespace LaravelMagic\Backend\Models;


use \Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \LaravelMagic\Backend\Traits\HasFilters;

class BaseModel extends Model
{
    use HasFilters;

    protected $imageable = [];



}
