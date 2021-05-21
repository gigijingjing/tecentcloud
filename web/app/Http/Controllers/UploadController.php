<?php

namespace App\Http\Controllers;

use Storage;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    protected function postEditorUpload(Request $request)
    {
        $files = $request->file();
        $images = [];
        foreach($files as $key => $file) {
             $imagePath = $file->store('/public/' . date('Y-m-d'). '/editor');
             $images[] = Storage::url($imagePath);
        }
        return response()->json([
            'errno' => 0,
            'data' => $images
        ]);
    }
}
