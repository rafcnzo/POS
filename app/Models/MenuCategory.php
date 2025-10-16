<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuCategory extends Model
{
    use HasFactory;

    protected $guarded = []; // Izinkan mass assignment

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class);
    }
}
