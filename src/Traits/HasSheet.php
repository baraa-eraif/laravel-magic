<?php

namespace LaravelMagic\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LaravelMagic\Export\BaseExport;
use Maatwebsite\Excel\Facades\Excel;


trait HasSheet
{
    public $callableCollectionMethod = 'serializeForEdit';

    public $excelResource;

    public $sheetTitle;

    /***
     * @param Request $request
     * @return mixed
     */
    public function import(Request $request)
    {
        $request->validate($this->_getRequest()->imoprtingRules(), $this->_getRequest()->imoprtingMessages());
        $model = $this->_getRepository()->insert($request);
        return response()->api(SUCCESS_STATUS, trans('core::messages.fetched_successfully', ['attribute' => $this->alertMessage()]), $model, []);
    }

    public function exportExcel()
    {
        @ini_set('max_execution_time', -1);
        @ini_set('memory_limit', '2048M');

        $resource = strtolower(class_basename($this->modelClass));
        $collection = $this->collectedDataForSheets();
        $filename = $this->saveExcelFile($resource, [
            'collection' => $collection,
            'columns' => $this->columnsForSheets(),
        ]);
        return response()->api(SUCCESS_STATUS, trans('core::messages.excel_exported_successfully'), ['filename' => file_url($filename, true)]);
    }


    public function saveExcelFile(string $partials_path, $args = [])
    {
        // Get model and resource
        $model = _get($args, 'model', $this->modelClass);
        $resource = _get($args, 'resource', strtolower(class_basename($model)));

        // Store path
        $store_path = handle_store_file_path($partials_path, $resource);

        // Get class and collection
        $class = _get($args, 'class');
        $collection = $class && method_exists($class, 'collection')
            ? $class->collection()
            : _get($args, 'collection');

        // Get columns, falling back to default if necessary
        $columns = $class && method_exists($class, 'headings')
            ? $class->headings()
            : (_get($args, 'columns', []) ?: collect(Arr::first($collection))->keys());

        // Get title, falling back to resource name
        $title = $class && method_exists($class, 'title')
            ? $class->title()
            : $resource;

        // Store the Excel file
        Excel::store(new BaseExport($collection, $columns, $title, _get($args, 'translation_file')), $store_path);

        // Return the store path
        return $store_path;
    }


    public function exportPdf()
    {
        @ini_set("pcre.backtrack_limit", "5000000");
        $resource = strtolower(class_basename($this->modelClass));
        $path_file = save_pdf($this->viewPdf, $resource,
            ['columns' => $this->columnsForSheets()],
            ['mergeData' => ['collection' => $this->collectedDataForSheets(), 'model_name' => strtolower(class_basename($this->modelClass))], 'resource' => $resource]);

        return response()->api(SUCCESS_STATUS, trans('core::messages.excel_exported_successfully'), ['filename' => file_url($path_file, true)]);
    }



    public function columnsForSheets(): array
    {
        $model = (new $this->modelClass);
        if (method_exists($model, 'getColumnsForSheets'))
            return $model->getColumnsForSheets();
        return [];
    }

    public function collectedDataForSheets()
    {
        $resource = $this->excelResource ?? $this->resourceClass;
        if (request()->get('id')) {
            $model = $this->repositoryInstance->find(request()->get('id'));
            return (new $resource($model))->{$this->callableCollectionMethod}(request());
        }
        request()->request->add(['no_pagination' => true]);
        return $resource::Collection($this->repositoryInstance->all())->toArray(request(), $this->callableCollectionMethod);
    }


}
