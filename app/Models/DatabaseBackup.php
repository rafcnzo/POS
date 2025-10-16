<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseBackup extends Model
{
    protected $fillable = ['file_name', 'file_path', 'file_size'];
}
