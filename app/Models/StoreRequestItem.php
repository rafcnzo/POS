<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequestItem extends Model
{
    protected $table = 'store_request_items';
    protected $fillable = ['store_request_id', 'ingredient_id', 'requested_quantity', 'issued_quantity'];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
