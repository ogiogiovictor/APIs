<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadImagesController extends Controller
{
    public function storeImages(Request $request){

        $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,gif|max:2048',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $fileName);
        

    }
}
