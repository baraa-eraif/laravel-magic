<?php

namespace LaravelMagic\Enum;


enum BasicEnum
{

    const MODEL_BASE_PATH = "App\\Models\\";

    const BASE_PROPERTY_BASE_PATH = 'LaravelMagic';

    const BASE_PROPERTY_IDENTIFIER = 'Class';

    const MODEL_TARGET = 'Model';

    const RESOURCE_TARGET = 'Resource';

    const REPOSITORY_TARGET = 'Repository';

    const REQUEST_TARGET = 'request_target';


    const DEFAULT_REPOSITORY_PATH = 'LaravelMagic\\Repositories\\BaseRepository';

    const DEFAULT_RESOURCE_PATH = 'LaravelMagic\\Http\\Resources\\BaseResource';

    const DEFAULT_REQUEST_PATH = 'LaravelMagic\\Http\\Requests\\BaseRequest';

    const DEFAULT_MIDDLEWARE_PATH = '';

    const NO_PAGINATION_KEY = 'no_pagination';

    public static function callProperty(string $key)
    {
        return constant("self::$key");
    }

}
