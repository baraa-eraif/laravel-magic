<?php

namespace LaravelMagic\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelMagic\Http\Resources\AuthResource;
use LaravelMagic\Repositories\AuthRepository;

class AuthController extends Controller
{
    protected $authRepository;
    protected $guard = 'admin-api';
    protected $model = User::class;
    protected $resource = AuthResource::class;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
        $this->authRepository->setGuard($this->guard);
        $this->authRepository->setModelPath($this->model);
    }

    /**
     * User login
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $this->credentials($request);
        $token = $this->authRepository->login($credentials);

        if (!$token) {
            return $this->unauthenticated();
        }

        $this->authenticated($token);

        return response()->api(SUCCESS_STATUS, trans('core::messages.successfully_logged_in'), [
            'access_token' => $token,
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'token_type' => 'Bearer',
            'auth' => (new $this->resource(auth($this->guard)->user()))->toArray($request),
        ]);
    }

    /**
     * Get credentials from request
     *
     * @param Request $request
     * @return array
     */
    public function credentials(Request $request)
    {
        return [
            $this->username() => $request->get($this->username()),
            $this->password() => $request->get($this->password())
        ];
    }

    /**
     * Get username field
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Get password field
     *
     * @return string
     */
    public function password()
    {
        return 'password';
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $user = $this->authRepository->logout();

        if ($user) return response()->api(SUCCESS_STATUS, trans('api::lang.logged_out_successfully'));
        return $this->unauthenticated();
    }

    /**
     * Refresh token
     *
     * @param string $auth
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh($auth = 'api')
    {
        $token = $this->authRepository->refresh($auth);

        if ($token) {
            return $this->authenticated($token);
        }

        return response()->api(ERROR_STATUS, trans('Auth::lang.token_refreshed_error'));
    }

    /**
     * Handle successful authentication
     *
     * @param $token
     */
    protected function authenticated($token)
    {
        // You can add custom logic here after authentication (logging, analytics, etc.)
    }

    /**
     * Handle unauthenticated responses
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthenticated($message = 'user_not_found')
    {
        return response()->json([
            'status' => false,
            'message' => trans("core::messages.$message"),
            'error_code' => 422,
            'data' => null
        ], 422);
    }

    /**
     * Get the guard
     *
     * @return mixed
     */
    public function guard()
    {
        return Auth::guard($this->authRepository->getGuard());
    }
}
