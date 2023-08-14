<?php

namespace LaravelMagic\Backend\Contracts;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface HookInterface
{

    public function beforeCreate(Request $request);

    public function beforeSaving(Request $request);

    public function created(Request $request,Model $model);

    public function updated(Request $request,Model $model);

    public function saving(Request $request,Model $model);
}
