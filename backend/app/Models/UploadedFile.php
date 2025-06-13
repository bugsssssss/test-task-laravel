<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    protected $fillable = [
        'client_code',
        'file_unique_id',
        'type',
        'size',
        'path',
        'created_at',
    ];
}
