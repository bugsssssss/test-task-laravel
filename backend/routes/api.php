<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\FileDownloadController;



// ? загрузка в form-data 
Route::post('/upload/file', [FileUploadController::class, 'upload']);
// ? загрузка base64
Route::post('/upload/base64', [FileUploadController::class, 'baseUpload']);
// ? полчуить по коду 
Route::get('/file/{code}', [FileDownloadController::class, 'download']);
// ? получить по айди 
Route::get('/file/id/{id}', [FileDownloadController::class, 'downloadById']);
// ? по клиенту 
Route::get('/files', [FileDownloadController::class, 'list']);