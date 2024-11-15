<?php

namespace LaravelMagic\Providers;

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
            // Prepare base payload
            $payload = [
                'status'    => $status,
                'message'   => $message,
                'error_code'=> $error_code,
            ];

            // Handle paginated data
            if ($data instanceof LengthAwarePaginator) {
                $data = pagination($data);
                // Merge paginated data with the base payload
                $payload = array_merge($payload, $data);
            } else {
                // Add non-paginated data to the payload
                $payload['data'] = $data;
            }

            // Include extra data if provided
            if ($extra) {
                $payload['extra'] = $extra;
            }

            // Return the response with the proper JSON header
            return Response::make($payload, $statusCode, ['Content-Type' => 'application/json']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // No implementation needed for boot
    }


}
