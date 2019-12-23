<?php

namespace App\Api\Requests;

// use Illuminate\Http\Request as FormRequest;
use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{

    public function authorize()
    {
        return true;
    }
}
