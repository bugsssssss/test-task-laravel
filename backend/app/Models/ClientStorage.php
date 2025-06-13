<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientStorage extends Model
{
    protected $table = 'client_storage';

    protected $fillable = [
        'client_code', 'size_bytes',
    ];
}
