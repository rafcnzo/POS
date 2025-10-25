<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FfneStockAdj extends Model
{
    use HasFactory;

    protected $fillable = [
        'ffne_id',
        'user_id', 
        'type',
        'quantity', 
        'stock_before', 
        'stock_after', 
        'notes',
        'referenceable_id', 
        'referenceable_type', 
    ];

    /**
     * Get the FFNE associated with this stock adjustment.
     */
    public function ffne()
    {
        return $this->belongsTo(Ffne::class, 'ffne_id');
    }
}
