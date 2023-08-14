<?php

namespace LaravelMagic\Backend\Traits;

use Illuminate\Support\Str;
use LaravelMagic\Backend\Enum\BasicEnum;
use LaravelMagic\Backend\Exceptions\BaseException;
use LaravelMagic\Backend\Repositories\BaseRepository;
use LaravelMagic\Backend\Http\Resources\BaseResource;

trait Base
{
    /**
     * @var string
     * @author Baraa
     */
    protected $modelClass;
    /**
     * @var string
     * @author Baraa
     */
    protected $resourceClass;
    /**
     * @var string
     * @author Baraa
     */
    protected $requestClass;
    /**
     * @var string
     * @author Baraa
     */
    protected $middlewareClass;
    /**
     * @var string
     * @author Baraa
     */
    protected $repositoryClass;
    /**
     * @var string
     * @author Baraa
     */
    protected $repositoryInstance;

    /**
     * @var string
     * @author Baraa
     */
    private function __init()
    {
        $this->refliction = new \ReflectionClass($this);

        if (!$this->modelClass)
            $this->modelClass = $this->defindModel();

        foreach ($this->refliction->getProperties() as $property)
            if (str_contains($property->name, BasicEnum::BASE_PROPARTY_IDENTIFIER))
                if (!$this->{$property->name})
                    $this->defindClass($property);

        $this->repositoryInstance = app()->make($this->repositoryClass);
        $this->repositoryInstance->setModel($this->modelClass);
        $this->repositoryInstance->setResource($this->resourceClass);
    }

    public function __call($method, $arguments)
    {
        $property = lcfirst(substr($method, 3));
        if (strncasecmp($method, 'get', 3) === 0) {
            if (property_exists($this, $property . 'Class'))
                return new $this->{$property . 'Class'};
            return $this->$property ?? null;
        } elseif (strncasecmp($method, 'set', 3) === 0) {
            if (count($arguments) !== 1)
                throw new \InvalidArgumentException("{$method}() expects exactly 1 argument.");
            $this->$property = $arguments[0];
        } else
            throw new \BadMethodCallException("Method {$method} does not exist.");
        dd($property, $method, $arguments);
    }

    /**
     * @return string
     * @throws BASEException
     * @author Baraa
     */
    private function defindModel()
    {
        $class_path = BasicEnum::MODEL_BASE_PATH . Str::headline(Str::remove('Controller', $this->getBaseClassPath()));
        if (!class_exists($class_path))
            throw new BaseException('Model not support');
        return $class_path;
    }

    /**
     * @param $reflictionTarget
     * @return void
     * @author Baraa
     */
    private function defindClass($reflictionTarget)
    {
        $targetClass = $this->classNameSpace($reflictionTarget->name);
        $this->{$reflictionTarget->name} = class_exists($targetClass) ? $targetClass : $this->defaultClass($reflictionTarget);
    }

    /**
     * @param $reflictionTarget
     * @return mixed
     * @author Baraa
     */
    private function defaultClass($reflictionTarget)
    {
        $base = Str::upper($this->handleTargetClassName($reflictionTarget->name));
        return BasicEnum::callProporty("DEFAULT_{$base}_PATH");
    }

    /**
     * @return string
     * @author Baraa
     */
    private function getBaseClassPath()
    {
        return basename(get_class($this));
    }


    private function classNameSpace($directory)
    {
        $class = ucfirst($this->handleTargetClassName($directory));
        $namespace = str_replace(["Controllers", "Controller"], [Str::plural($class), $class], get_class($this));
        return str_contains($namespace, '\Repositories') ? Str::remove('\Http', $namespace) : $namespace;
    }

    private function handleTargetClassName($directory)
    {
        return Str::remove('Class', $directory);
    }
}
