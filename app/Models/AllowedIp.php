<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedIp extends Model
{
    protected $table = 'allowed_ips';

    protected $fillable = [
        'ip',
        'label'
    ];
}
