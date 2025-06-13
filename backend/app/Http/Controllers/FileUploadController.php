<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use App\Models\ClientStorage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
                'type' => 'required|in:leads,deals,tasks,products,chats',
                'client_code' => 'required|string',
                'file_unique_id' => 'required|string|unique:uploaded_files,file_unique_id',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 400);
        };

        $file = $request->file('file');
        $type = $request->type;
        $clientCode = $request->client_code;
        $fileUniqueId = $request->file_unique_id;

        $path = "uploads/{$clientCode}/{$type}";
        $storedPath = $file->store($path, 'local'); // ? stored in storage/app/uploads/...

        $size = $file->getSize();

        $uploadedFile = UploadedFile::create([
            'file_unique_id' => $fileUniqueId,
            'client_code' => $clientCode,
            'type' => $type,
            'path' => $storedPath,
            'size' => $size,
        ]);

        // ? обновить общий размер файлов клиента, например:
        $clientStorage = ClientStorage::firstOrNew(['client_code' => $clientCode]);
        $clientStorage->size_bytes = ($clientStorage->size_bytes ?? 0) + $size;
        $clientStorage->save();
        

        return response()->json([
            'id' => $uploadedFile->id,
            'file_unique_id' => $fileUniqueId,
            'path' => $storedPath,
            'size' => $size,
        ]);
    }

    public function baseUpload(Request $request)
    {
        try {
            $request->validate([
                'file_base64' => 'required|string',
                'type' => 'required|in:leads,deals,tasks,products,chats',
                'client_code' => 'required|string',
                'filename' => 'required|string',
                'file_unique_id' => 'nullable|string|unique:uploaded_files,file_unique_id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 400);
        };

        $base64Data = $request->file_base64;
        $clientCode = $request->client_code;
        $type = $request->type;
        $filename = $request->filename;
        $fileUniqueId = $request->file_unique_id ?? Str::uuid()->toString();

        // ? Распаковываем base64
        try {
            if (preg_match('/^data:\w+\/\w+;base64,/', $base64Data)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            }

            $decoded = base64_decode($base64Data);

            if ($decoded === false) {
                return response()->json(['error' => 'Invalid base64 string'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Base64 decoding failed'], 400);
        }

        // ? Сохраняем файл
        $path = "uploads/{$clientCode}/{$type}";
        $storedPath = "{$path}/" . uniqid() . '_' . $filename;

        Storage::disk('local')->put($storedPath, $decoded);
        $size = strlen($decoded);

        // ? Сохраняем в базу
        $uploadedFile = UploadedFile::create([
            'file_unique_id' => $fileUniqueId,
            'client_code' => $clientCode,
            'type' => $type,
            'path' => $storedPath,
            'size' => $size,
        ]);


        // ? обновить общий размер файлов клиента, например:
        ClientStorage::updateOrCreate(
            ['client_code' => $clientCode],
            ['size_bytes' => \DB::raw("size_bytes + $size")]
        );

        return response()->json([
            'id' => $uploadedFile->id,
            'file_unique_id' => $fileUniqueId,
            'path' => $storedPath,
            'size' => $size,
        ]);
    }
}
