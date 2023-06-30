<?php

namespace Nawa\Backend\Models;


use \Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \Nawa\Backend\Traits\HasFilters;

class BaseModel extends Model
{
    use HasFilters;

    protected $imageable = [];



}
