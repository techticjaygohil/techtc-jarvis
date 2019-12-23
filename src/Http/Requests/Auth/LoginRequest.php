<?php

namespace Jarvis\Http\Requests\Auth;

class LoginRequest extends Request
{
	public function rules()
	{
		//dd('test');
		return [
			'email' => 'required|string|max:255',
			'password' => 'required|string',
            'device_token' => 'nullable',
            'device_type'       => 'nullable|in:ios,android',
		];
	}
}
