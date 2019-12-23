<?php

namespace Jarvis\Http\Requests\Auth;

class Request extends Request
{

    public function authorize()
    {
        return true;
    }
}
