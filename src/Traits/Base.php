<?php

namespace LaravelMagic\Traits;

use Illuminate\Support\Str;
use LaravelMagic\Enum\BasicEnum;
use LaravelMagic\Exceptions\BaseException;
use LaravelMagic\Repositories\BaseRepository;
use LaravelMagic\Http\Resources\BaseResource;

trait Base
{
    protected $modelClass;
    protected $resourceClass;
    protected $repositoryClass;
    protected $repositoryInstance;


    public function __construct()
    {
        $this->__init();
    }

    /**
     * Initializes the trait by setting class dependencies and repository.
     */
    private function __init()
    {
        $this->refliction = new \ReflectionClass($this);

        // Define model if not set
        if (!$this->modelClass) {
            $this->modelClass = $this->defineModel();
        }

        // Define any other required classes if not set
        foreach ($this->refliction->getProperties() as $property) {
            if (str_contains($property->name, BasicEnum::BASE_PROPERTY_IDENTIFIER) && !$this->{$property->name}) {
                $this->defineClass($property);
            }
        }

        // Initialize repository
        $this->repositoryInstance = app()->make($this->repositoryClass);
        $this->repositoryInstance->setModel($this->modelClass);
        $this->repositoryInstance->setResource($this->resourceClass);
    }

    /**
     * Magic method to handle dynamic getter and setter methods.
     */
    public function __call($method, $arguments)
    {
        $property = lcfirst(substr($method, 3));

        if (strncasecmp($method, 'get', 3) === 0) {
            // Return the class instance or property value
            return property_exists($this, $property . 'Class')
                ? new $this->{$property . 'Class'}
                : $this->$property ?? null;
        }

        if (strncasecmp($method, 'set', 3) === 0) {
            if (count($arguments) !== 1) {
                throw new \InvalidArgumentException("{$method}() expects exactly 1 argument.");
            }
            $this->$property = $arguments[0];
        } else {
            throw new \BadMethodCallException("Method {$method} does not exist.");
        }
    }

    /**
     * Defines the model class based on the controller name.
     *
     * @throws BaseException
     */
    private function defineModel()
    {
        $classPath = BasicEnum::MODEL_BASE_PATH . Str::headline(Str::remove('Controller', $this->getBaseClassPath()));

        if (!class_exists($classPath)) {
            throw new BaseException('Model not supported.');
        }

        return $classPath;
    }

    /**
     * Defines the class based on reflection target.
     */
    private function defineClass($reflectionTarget)
    {
        $targetClass = $this->getClassNamespace($reflectionTarget->name);
        $this->{$reflectionTarget->name} = class_exists($targetClass)
            ? $targetClass
            : $this->getDefaultClass($reflectionTarget);
    }

    /**
     * Returns the default class if the class is not found.
     */
    private function getDefaultClass($reflectionTarget)
    {
        $base = Str::upper($this->handleClassName($reflectionTarget->name));
        return BasicEnum::callProperty("DEFAULT_{$base}_PATH");
    }

    /**
     * Gets the base class path (controller name).
     */
    private function getBaseClassPath()
    {
        return basename(get_class($this));
    }

    /**
     * Generates the class namespace based on the directory name.
     */
    private function getClassNamespace($directory)
    {
        $className = ucfirst($this->handleClassName($directory));
        $namespace = str_replace(["Controllers", "Controller"], [Str::plural($className), $className], get_class($this));

        return str_contains($namespace, '\Repositories')
            ? Str::remove('\Http', $namespace)
            : $namespace;
    }

    /**
     * Removes "Class" from the class name.
     */
    private function handleClassName($directory)
    {
        return Str::remove('Class', $directory);
    }
}
