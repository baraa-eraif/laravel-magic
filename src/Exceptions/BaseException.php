<?php

namespace LaravelMagic\Backend\Exceptions;

use Exception;

class BaseException extends Exception
{
    protected $errorMessage;

    /**
     * Create a new CustomException instance.
     *
     * @param  string  $errorMessage
     * @return void
     */
    public function __construct($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        parent::__construct();
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        // Custom logic for rendering the exception
        // You can return a custom error view or JSON response

        if ($request->expectsJson()) {
            return response()->json(['error' => $this->errorMessage], 500);
        }

        return response()->view('errors.custom', ['message' => $this->errorMessage], 500);
    }
}
