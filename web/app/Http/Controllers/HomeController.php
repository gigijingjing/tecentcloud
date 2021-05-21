<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class HomeController extends Controller
{
    public function __construct()
    {
    }

    public function getIndex(Request $request)
    {
        return redirect('/app');
    }
}
