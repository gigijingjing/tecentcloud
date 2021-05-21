<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    protected function responseJson($data = array(), $code = 200)
    {
        return response()->json($data, $code);
    }

    protected function responseError($message = '', $code = 500)
    {
        return response()->json([
            'message' => $message
        ], $code);
    }
}
