<?php

namespace App\Api\Requests\Auth;

class UpdateRegisterRequest extends Request
{
    public function rules()
    {
        return [
            'first_name'       => 'nullable|string|max:255',
            'last_name'        => 'nullable|string|max:255',
            'email'            => 'nullable|string|email|max:255|unique:users',
            'phone'            => 'nullable|string',
            'profile_pic'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
        ];  
    }
}
