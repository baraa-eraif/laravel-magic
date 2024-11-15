<?php

namespace LaravelMagic\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{

    public function upload(Request $request)
    {
        $attachment = $request->file('file');
        try {
            $storage = store_attachment($attachment);

            return response()->api(SUCCESS_STATUS, 'File uploaded successfully!', $storage);
        } catch (\Exception $e) {
            return response()->api(ERROR_STATUS, $e->getMessage());
        }
    }


    public function exportFile(string $arg, string $arg1, ?string $arg2 = null, ?string $arg3 = null)
    {
        $pathArg = [$arg, $arg1, $arg2, $arg3 ?? ''];

        $path = 'app/public/' . implode('/', array_filter($pathArg));

        return $this->retrieveFile($path);
    }

    public function retrieveFile($path)
    {
        $path = storage_path($path);

        if (!file_exists($path))
            abort(404);

        return response()->download($path);
    }

}
