<?php


namespace LaravelMagic\Http\Resources;



class AuthResource extends BaseResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'is_super_admin' => $this->isSuperAdmin(),
        ];
    }
}
