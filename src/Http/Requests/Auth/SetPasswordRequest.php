<?php

namespace App\Api\Requests\Auth;

class SetPasswordRequest extends Request
{
    public function rules()
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'token'    => 'required',
        ];
    }
}
