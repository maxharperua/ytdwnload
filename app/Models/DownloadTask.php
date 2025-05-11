<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadTask extends Model
{
    protected $table = 'download_tasks';
    protected $fillable = [
        'url', 'format', 'type', 'status', 'progress', 'file_path', 'error'
    ];
} 