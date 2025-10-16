<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Ingredient;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
