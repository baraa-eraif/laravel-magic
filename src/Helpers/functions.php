<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;


function pagination(LengthAwarePaginator $paginator)
{
    $data = $paginator->getCollection();

    $result = [
        'data' => $data,
        'paginator' => $paginator->toArray(),
    ];

    unset($result['paginator']['data']);

    return $result;
}

function _get(...$args)
{
    return Arr::get(...$args);
}


if (!function_exists('upload_show_response')) {
    function upload_show_response($file)
    {
        return [
            [
                'id' => $file,
                'name' => basename($file),
                'uid' => $file,
                'status' => true,
                'url' => '/' . Str::replace('public/', STORAGE_FILES_PATH_PREFIX, $file),
            ]
        ];
    }
}

if (!function_exists('handle_store_file_path')) {
    function handle_store_file_path($partialPath, $file_prefix = 'file', $extension = "xlsx")
    {
        return "$partialPath/" . generate_storage_file_name($file_prefix, $extension);
    }
}
if (!function_exists('generate_storage_file_name')) {
    function generate_storage_file_name($prefix, $extension)
    {
        return "{$prefix}_" . now()->format('d-m-Y') . '.' . $extension;
    }
}
if (!function_exists('preview_file_url')) {
    function preview_file_url($file_url)
    {
        if (!$file_url)
            return null;
        return url(STORAGE_FILES_PATH_PREFIX . 'preview/' . Str::replace('public/', '', $file_url));
    }
}

if (!function_exists('preview_file_url_api')) {
    function preview_file_url_api($file_url)
    {
        if (!$file_url)
            return null;
        return url('api/' . STORAGE_FILES_PATH_PREFIX . 'preview/' . Str::replace('public/', '', $file_url));
    }
}

if (!function_exists('save_pdf')) {
    function save_pdf(string $view, $path, $data, $config = array())
    {
        try {
            $resource = _get($config, 'resource', 'pdf_report');
            $pdf = LaravelMpdf::loadView($view, $data, _get($config, 'mergeData', []),
                array_merge([
                    'title' => $resource,
                    'format' => _get($config, 'format', 'A4-L'),
                    'orientation' => _get($config, 'orientation', 'L')
                ], _get($config, 'config', []))
            );
            return store_attachment($path, $pdf->output(), $resource, 'pdf');
        } catch (Exception $exception) {
            \Illuminate\Support\Facades\Log::error("save_pdf exception" . $exception->getMessage());
        }
    }
}

if (!function_exists('store_attachment')) {
    function store_attachment($attachment, $args = [])
    {
        try {
            $extension = $attachment->getClientOriginalExtension();
            $store_path = handle_store_file_path('base','file',$extension);
            return \Illuminate\Support\Facades\Storage::disk(_get($args, 'disk', 'public'))->put($store_path, $attachment);
        } catch (\Exception $e) {
            dd($e);
            return response()->api(ERROR_STATUS, 'Failed to upload the file. Please try again. ' . $e->getMessage());
        }
    }
}

if (!function_exists('file_url')) {
    function file_url($file_url, bool $withOutBaseUrl = false, $withPrefix = false)
    {
        if (!$file_url)
            return null;

        $file_url = STORAGE_FILES_PATH_PREFIX . $file_url;

        return $withOutBaseUrl ? $file_url : url($file_url);
    }
}
