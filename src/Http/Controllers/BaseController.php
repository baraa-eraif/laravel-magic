<?php

namespace LaravelMagic\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LaravelMagic\Contracts\BaseInterface;
use LaravelMagic\Contracts\HookInterface;
use LaravelMagic\Enum\BasicEnum;
use LaravelMagic\Traits\Base;
use LaravelMagic\Traits\HasSheet;

class BaseController extends Controller implements BaseInterface, HookInterface
{
    use Base, HasSheet;

    /**
     * @var bool
     */
    public $indexNoPagination = false;

    /**
     * BaseController constructor.
     */


    /**
     * Get all resources (with optional pagination).
     */
    public function index()
    {
        $args = $this->indexNoPagination ? [BasicEnum::NO_PAGINATION_KEY => true] : [];
        $model = $this->repositoryInstance->all($args);
        return $this->sendResponse($model);
    }

    /**
     * Store a new resource.
     */
    public function store(Request $request)
    {
        $this->validateRequest($request);
        $this->beforeCreate($request);
        $model = $this->repositoryInstance->store($request);
        $this->created($request, $model);
        return $this->sendResponse(new $this->resourceClass($model));
    }

    /**
     * Update an existing resource.
     */
    public function update(Request $request, $id)
    {
        $this->validateRequest($request);
        $this->beforeUpdate($request);
        $model = $this->repositoryInstance->update($request, $id);
        $this->updated($request, $model);
        return $this->sendResponse(new $this->resourceClass($model));
    }

    /**
     * Show a single resource.
     */
    public function show($id)
    {
        $model = $this->repositoryInstance->find($id);
        return $this->sendResponse(new $this->resourceClass($model));
    }

    /**
     * Delete a resource.
     */
    public function destroy($id)
    {
        $model = $this->repositoryInstance->find($id);
        $model->delete();
        return $this->sendResponse(new $this->resourceClass($model));
    }

    /**
     * Handle actions before creating a resource.
     */
    public function beforeCreate(Request $request)
    {
        return $this->beforeSaving($request);
    }

    /**
     * Handle actions before updating a resource.
     */
    public function beforeUpdate(Request $request)
    {
        return $this->beforeSaving($request);
    }

    /**
     * Common pre-save logic for both create and update.
     */
    public function beforeSaving(Request $request)
    {
        // Add custom pre-save logic here (e.g., validations, transformations).
    }

    /**
     * Actions to perform after creating a resource.
     */
    public function created(Request $request, $model)
    {
        return $this->saving($request, $model);
    }

    /**
     * Actions to perform after updating a resource.
     */
    public function updated(Request $request, $model)
    {
        return $this->saving($request, $model);
    }

    /**
     * Perform common save logic.
     */
    public function saving(Request $request, $model)
    {
        // Perform any additional actions here.
        return $model;
    }

    /**
     * Validate the request.
     */
    private function validateRequest(Request $request)
    {
        $request->validate($this->getRequest()->rules(), $this->getRequest()->messages());
        return $this;
    }

    /**
     * Send a standardized API response.
     */
    private function sendResponse($data, $message = 'fetched_successfully')
    {
        return response()->api(SUCCESS_STATUS, $message, $data);
    }


}
