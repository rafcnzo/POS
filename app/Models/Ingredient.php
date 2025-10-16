<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'cost_price',
        'supplier_id', 
        'stock',       
        'minimum_stock', 
    ];

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'ingredient_menu_item')
                    ->withPivot('quantity');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
