<?php

namespace App\Api\Requests\Auth;

class ForgetPasswordRequest extends Request
{
    public function rules()
    {
        return [
            'email' => 'required|string|email|max:50',
        ];
    }
}
