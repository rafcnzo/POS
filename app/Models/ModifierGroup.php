<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Modifier;

class ModifierGroup extends Model
{
    protected $fillable = [
        'name',
        'selection_type',
    ];

    public function modifiers()
    {
        return $this->hasMany(Modifier::class);
    }
}
