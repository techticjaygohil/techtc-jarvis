<?php

namespace Jarvis\Http\Controllers;

use App\Http\Controllers\Controller as ControllersController;

class JarvisController extends ControllersController {
    public function index() {
        // return view('jarvisview::index');
         return 'Index of JarvisController'; 
    }
    
    public function weather() {
        // return view('jarvisview::weather');
        return 'Average temperature in ahmedabad is ~18C';
    }
}
