<?php

namespace App\Api\Requests\Auth;

class ChangePasswordRequest extends Request
{
    public function rules()
    {
        return [
            'old_password'    => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:new_password',
        ];
    }
}
