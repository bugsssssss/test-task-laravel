<?php

namespace App\Http\Controllers;

use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileDownloadController extends Controller
{
    public function download(string $code)
    {
        //? файл по file_unique_id
        $file = UploadedFile::where('file_unique_id', $code)->first();

        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        //  ?  существует ли файл физически
        if (!Storage::disk('local')->exists($file->path)) {
            return response()->json(['error' => 'File missing on disk'], 404);
        }

        // ? в mimetype 
        $mimeType = Storage::disk('local')->mimeType($file->path);
        $filename = basename($file->path);
        $fileContents = Storage::disk('local')->get($file->path);

        // ? вкрнуть файл с заголовками
        return response($fileContents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Content-Length', strlen($fileContents));
    }
    public function downloadById(int $id)
    {
        //? файл по ID
        $file = UploadedFile::find($id);

        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // ? существует ли файл на диске
        if (!Storage::disk('local')->exists($file->path)) {
            return response()->json(['error' => 'File missing on disk'], 404);
        }

        $mimeType = Storage::disk('local')->mimeType($file->path);
        $filename = basename($file->path);
        $fileContents = Storage::disk('local')->get($file->path);

        return response($fileContents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Content-Length', strlen($fileContents));
    }

    public function list(Request $request)
    {
        $clientCode = $request->query('client_code');
        $type = $request->query('type');

        if (!$clientCode) {
            return response()->json(['error' => 'client_code is required'], 422);
        }

        $query = UploadedFile::where('client_code', $clientCode);

        if ($type) {
            $query->where('type', $type);
        }

        $files = $query->get([
            'id',
            'file_unique_id',
            'type',
            'size',
            'path',
            'created_at',
        ]);

        return response()->json($files);
    }
}
