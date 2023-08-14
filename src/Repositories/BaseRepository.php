<?php


namespace LaravelMagic\Backend\Repositories;


use App\Models\Test;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LaravelMagic\Backend\Contracts\HookInterface;
use LaravelMagic\Backend\Exceptions\BaseException;
use LaravelMagic\Backend\Http\Resources\BaseResource;
use LaravelMagic\Backend\Traits\HasFilters;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class BaseRepository implements HookInterface
{
    use HasFilters;

    /**
     * @var JsonResource
     * @author Baraa
     */
    protected $resource;

    /**
     * @var Model
     * @author Baraa
     */
    protected $model;

    /**
     * @var String
     * @author Baraa
     */
    const ORDERED_COLUMN = 'id';

    /**
     * @var String
     * @author Baraa
     */
    const ORDER_DIRECTION = 'DESC';

    /**
     * @var Array
     * @author Baraa
     */
    const WITH_QUERY = [];

    /**
     * @var Integer
     * @author Baraa
     */
    const PET_PAGE = 10;

    /**
     * @param $method
     * @param $arguments
     * @return void|null
     * @author Baraa
     */
    public function __call($method, $arguments)
    {
        $property = lcfirst(substr($method, 3)); // Get the property name from the method name

        if (strncasecmp($method, 'get', 3) === 0) {
            // Getter logic
            return $this->$property ?? null;
        } elseif (strncasecmp($method, 'set', 3) === 0) {
            // Setter logic
            if (count($arguments) !== 1)
                throw new \InvalidArgumentException("{$method}() expects exactly 1 argument.");


            $this->$property = $arguments[0];
        } else {
            throw new \BadMethodCallException("Method {$method} does not exist.");
        }
    }

    /**
     * @param ...$args
     * @return mixed
     * @author Baraa
     */
    public function all(...$args)
    {
        $query = $this->getQurey();
        if (isset($args['no_pagination']))
            return $this->getResource()::collection($query->get());

        $result = $query->paginate($this->getDefaultPagination());
        $resourceResult = $this->getResource()::collection($result->getCollection());
        $result->setCollection(collect($resourceResult));
        return $result;
    }

    /**
     * @return mixed
     * @author Baraa
     */
    public function getBasicQurey()
    {
        return $this->getModel()->search(request());
    }

    /**
     * @return mixed
     * @author Baraa
     */
    public function getQurey()
    {
        $query = $this->getBasicQurey()->orderBy(self::ORDERED_COLUMN, self::ORDER_DIRECTION);
        if (self::WITH_QUERY)
            $query->with(self::WITH_QUERY);
        return $query;
    }

    /**
     * @return mixed
     * @author Baraa
     */
    protected function getModel()
    {
        return new $this->model;
    }

    /**
     * @param $id
     * @return mixed
     * @throws BaseException
     * @author Baraa
     */
    public function find($id)
    {
        $model = $this->getQurey()->find($id);
        if (is_null($model))
            throw new BaseException("Model Has a $id not found");
        return $model;
    }

    /**
     * @param $cols
     * @return mixed
     * @author Baraa
     */
    public function get($cols = ['*'])
    {
        return $this->getResource()::collection($this->getProcessedQuery()->get($cols));
    }

    /**
     * @param $id
     * @return mixed
     * @throws BaseException
     * @author Baraa
     */
    protected function delete($id)
    {
        $model = $this->find($id);
        $model->delete();
        return $model;
    }

    /**
     * @param Request $request
     * @return array
     * @author Baraa
     */
    public function getAttrbutes(Request $request)
    {
        return $request->all();
    }

    /**
     * @param Request $request
     * @return mixed
     * @author Baraa
     */
    public function store(Request $request)
    {
        $model = $this->getModel()->create($this->getAttrbutes($request));
        $this->beforeCreate($request)->created($request, $model);
        return $model;
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Support\HigherOrderTapProxy|mixed
     * @throws BaseException
     * @author Baraa
     */
    public function update(Request $request, $id)
    {
        $model = $this->find($id);

        $this->beforeUpdate($request, $model);

        $model->update($this->getAttrbutes($request));

        return tap($model->refresh(), function ($refreshedModel) use ($request) {
            return $this->updated($request, $refreshedModel);
        });
    }

    /**
     * @param Request $request
     * @return $this
     * @author Baraa
     */
    public function beforeCreate(Request $request)
    {
        //
        return $this->beforeSaving($request);
    }

    /**
     * @param Request $request
     * @return $this
     * @author Baraa
     */
    public function beforeUpdate(Request $request)
    {
        //
        return $this->beforeSaving($request);
    }

    /**
     * @param Request $request
     * @return $this
     * @author Baraa
     */
    public function beforeSaving(Request $request)
    {
        return $this;
    }

    /**
     * @param Request $request
     * @param $model
     * @return void
     * @author Baraa
     */
    public function created(Request $request, $model)
    {
        $this->saving($request, $model);
    }

    /**
     * @param Request $request
     * @param $model
     * @return void
     * @author Baraa
     */
    public function updated(Request $request, $model)
    {
        $this->saving($request, $model);
    }

    /**
     * @param Request $request
     * @param $model
     * @return Model
     * @author Baraa
     */
    public function saving(Request $request, $model)
    {
        return $model;
    }

}
