<?php


namespace LaravelMagic\Repositories;


use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class AuthRepository
{
    protected $modelPath = User::class;
    protected $guard;

    /**
     * Login user
     *
     * @param array $credentials
     * @return string|false
     * @throws AuthenticationException
     */
    public function login(array $credentials)
    {
        Config::set('jwt.user', $this->modelPath);
        Config::set('auth.providers.users.model', $this->modelPath);

        if (!$token = $this->guard()->attempt($credentials)) {
            return false;
        }
        return $token;
    }

    /**
     * Logout the user
     *
     * @param string $auth
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function logout($auth)
    {
        $user = auth($auth)->user();
        auth($auth)->logout();
        return $user;
    }

    /**
     * Refresh the token
     *
     * @param string $auth
     * @return string|null
     */
    public function refresh($auth)
    {
        if (auth($auth)->check()) {
            return auth($auth)->refresh();
        }

        return null;
    }

    /**
     * Get the guard
     *
     * @return mixed
     */
    public function guard()
    {
        return Auth::guard($this->getGuard());
    }

    /**
     * Get model path
     *
     * @return string
     */
    public function getModelPath(): string
    {
        return $this->modelPath;
    }

    /**
     * Set model path
     *
     * @param string $modelPath
     */
    public function setModelPath(string $modelPath): void
    {
        $this->modelPath = $modelPath;
    }

    /**
     * Get the guard
     *
     * @return string
     */
    public function getGuard(): string
    {
        return $this->guard;
    }

    /**
     * Set the guard
     *
     * @param string $guard
     */
    public function setGuard(string $guard): void
    {
        $this->guard = $guard;
    }
}
