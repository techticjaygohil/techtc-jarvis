<?php

namespace Jarvis\Http\Requests\Auth;

class RegisterRequest extends Request
{
    public function rules()
    {
        return [
            // 'first_name'            => 'nullable|max:100|string',
            // 'last_name'             => 'nullable|max:100|string',
            'name'         => 'required|max:100|string',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:8',
            'phone'        => 'nullable|max:20',
            'device_token' => 'nullable',
            'device'       => 'nullable',
        ];
    }
}
