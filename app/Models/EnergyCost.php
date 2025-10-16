<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyCost extends Model
{
    protected $fillable = [
        'name',
        'cost',
        'period',
    ];
}
