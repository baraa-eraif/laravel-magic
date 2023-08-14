<?php

namespace LaravelMagic\Backend\Providers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Response::macro('api', function ($status, $message, $data = [], $extra = [], $error_code = 0, $statusCode = 200) {

            if ($data instanceof LengthAwarePaginator) {
                $data = $this->pagination($data);
                $payload = array_merge(['status' => $status, 'message' => $message, 'error_code' => $error_code], $data);
            } else {
                $payload = ['status' => $status, 'message' => $message, 'error_code' => $error_code, 'data' => $data];
            }
            if (isset($extra))
                $payload = array_merge($payload, ['extra' => $extra]);

            return Response::make($payload, $statusCode,
                ['Content-Type' => 'application/json']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {


    }

    public function pagination(LengthAwarePaginator $lengthAwarePaginator)
    {

        $data = $paginator->getCollection();

        $result = [
            'data' => $data,
            'paginator' => $paginator->toArray(),
        ];

        unset($result['paginator']['data']);

        return $result;
    }

}
