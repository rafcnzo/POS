<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IngredientStockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id', 'user_id', 'type', 'quantity',
        'stock_before', 'stock_after', 'notes',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
