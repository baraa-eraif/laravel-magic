<?php

namespace LaravelMagic\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use LaravelMagic\Http\Controllers\BaseController;
use LaravelMagic\Http\Controllers\FileController;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected string $moduleNamespace = 'src/Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
//        $this->mapApiRoutes();
//
//        $this->mapWebRoutes();

        $this->resourceRoutes();

        $this->uploadingRoutes();

        $this->fileRoutes();

        $this->authApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(base_path('routes/api.php'));
    }


    public function resourceRoutes()
    {
        Route::macro('resourceRoutes', function ($resource, $controller, $function = null) {
            /**
             * Generate resource default rest-full routes
             *
             * @param $resource
             * @param string $controller
             * @return string
             * @author BaRaa
             */
            Route::resource($resource, $controller, ['parameters' => [$resource => 'id']]);
            Route::get("$resource/export/excel", "$controller@exportExcel");
            Route::get("$resource/export/pdf", "$controller@exportPdf");
            if (is_callable($function))
                Route::group(['prefix' => $resource], function () use ($function, $controller, $resource) {
                    call_user_func($function, $controller, $resource);
                });
            return $this;
        });
    }


    public function uploadingRoutes()
    {
        Route::macro('uploadingRoutes', function ($controller = FileController::class) {
            /**
             * Generate module uploading routes
             * Default controller AttachmentController
             *
             * @param string $controller
             * @return string
             * @author BaRaa
             */
            Route::group(['prefix' => 'upload', 'as' => 'upload.'], function () use ($controller) {
                Route::post('image', "$controller@upload");
            });

        });
    }


    public function fileRoutes()
    {
        Route::macro('fileRoutes', function ($controller = FileController::class) {
            /**
             * Generate module uploading routes
             * Default controller AttachmentController
             *
             * @param string $controller
             * @return string
             * @author BaRaa
             */
            Route::group(['prefix' => STORAGE_FILES_PATH_PREFIX], function () use ($controller) {
                Route::get('/preview/{arg}/{arg1}/{arg2?}/{arg3?}', "$controller@previewFile");
                Route::get('/{arg}/{arg1}/{arg2?}/{arg3?}', "$controller@exportFile");
            });

        });
    }


    public function authApiRoutes()
    {
        Route::macro('authApiRoutes', function ($controller = "AuthController") {
            /**
             * Generate module authentication routes
             * Default controller AuthController
             *
             * @param string $controller
             * @return string
             * @author BaRaa
             */
            Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () use ($controller) {
                Route::post('login', ['as' => 'login', 'uses' => "$controller@login"]);
                Route::get('logout', ['as' => 'logout', 'uses' => "$controller@logout"]);
                Route::get('refresh', ['as' => 'refresh', 'uses' => "$controller@refresh"]);
            });
            return $this;
        });
    }

}
