<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ModifierGroup;
use App\Models\Ingredient;

class Modifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'modifier_group_id',
        'name',
        'price',
        'ingredient_id',
        'quantity_used',
    ];

    /**
     * Sebuah pilihan (modifier) dimiliki oleh satu grup.
     */
    public function modifierGroup()
    {
        return $this->belongsTo(ModifierGroup::class);
    }

    /**
     * Sebuah pilihan (modifier) bisa terhubung ke satu bahan baku.
     */
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
