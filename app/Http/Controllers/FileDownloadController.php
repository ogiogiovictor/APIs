<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class FileDownloadController extends Controller
{
    public function show($folder, $filename)
    {
        $filePath = $folder . '/' . $filename;
        $file = Storage::get($filePath);

        $mimeType = Storage::mimeType($filePath);
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ];

        return response($file, 200, $headers);
    }
}
