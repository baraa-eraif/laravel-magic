<?php

namespace LaravelMagic\Backend\Enum;


enum BasicEnum
{

    const MODEL_BASE_PATH = "App\\Models\\";

    const BASE_PROPARTY_BASE_PATH = 'Nawa\\Backend';

    const BASE_PROPARTY_IDENTIFIER = 'Class';

    const MODEL_TARGET = 'Model';

    const RESOURCE_TARGET = 'Resource';

    const REPOSITORY_TARGET = 'Repository';

    const REQUEST_TARGET = 'request_target';


    const DEFAULT_REPOSITORY_PATH = 'Nawa\\Backend\\Repositories\\BaseRepository';

    const DEFAULT_RESOURCE_PATH = 'Nawa\\Backend\\Http\\Resources\\BaseResource';

    const DEFAULT_REQUEST_PATH = 'Nawa\\Backend\\Http\\Requests\\BaseRequest';

    const DEFAULT_MIDDLEWARE_PATH = '';

    const NO_PAGINATION_KEY = 'no_pagination';


    public static function callProporty(string $key)
    {
        return constant("self::$key");
    }

}
