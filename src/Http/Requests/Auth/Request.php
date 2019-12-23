<?php

namespace App\Api\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{

    public function authorize()
    {
        return true;
    }
}
