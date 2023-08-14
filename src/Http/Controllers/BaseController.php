<?php

namespace LaravelMagic\Backend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelMagic\Backend\Contracts\BaseInterface;
use LaravelMagic\Backend\Contracts\HookInterface;
use LaravelMagic\Backend\Enum\BasicEnum;
use LaravelMagic\Backend\Traits\Base;
use LaravelMagic\Backend\Traits\BaseControllerProperty;

class BaseController extends Controller implements BaseInterface, HookInterface
{
    use Base;

    /**
     * @var \ReflectionClass
     * @author Baraa
     */
    private $refliction;
    /**
     * @var Boolean
     * @author Baraa
     */
    public $indexNoPagination = false;


    public function __construct()
    {
        $this->__init();
    }

    public function index()
    {
        $args = [];
        if ($this->indexNoPagination)
            $args[BasicEnum::NO_PAGINATION_KEY] = true;

        $model = $this->repositoryInstance->all($args);
        return response()->json([true, 'fetched_successfully', $model]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request)->beforeCreate($request);
        $model = $this->repositoryInstance->store($request);
        $this->created($request, $model);
        return response()->json([true, 'fetched_successfully', new $this->resourceClass($model)]);
    }


    public function update(Request $request, $id)
    {
        $this->validateRequest($request)->beforeUpdate($request);
        $model = $this->repositoryInstance->update($request, $id);
        $this->updated($request, $model);
        return response()->json([true, 'fetched_successfully', new $this->resourceClass($model)]);
    }

    public function show($id)
    {
        $model = $this->repositoryInstance->find($id);
        return response()->json([true, 'fetched_successfully', new $this->resourceClass($model)]);
    }

    public function destroy($id)
    {
        $model = $this->repositoryInstance->find($id);
        $model->delete();
        return response()->json([true, 'fetched_successfully', new $this->resourceClass($model)]);
    }

    public function beforeCreate(Request $request)
    {
        $this->beforeSaving($request);
    }

    public function beforeUpdate(Request $request)
    {
        $this->beforeSaving($request);
    }

    public function beforeSaving(Request $request)
    {
        // TODO: Implement beforeSaving() method.
    }

    public function created(Request $request, $model)
    {
        return $this->saving($request, $model);
    }

    public function updated(Request $request, $model)
    {
        return $this->saving($request, $model);
    }

    public function saving(Request $request, $model)
    {
        return $model;
    }

    private function validateRequest(Request $request)
    {
        $request->validate($this->getRequest()->rules(), $this->getRequest()->messages());
        return $this;
    }
}
