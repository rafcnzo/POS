<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FfneStockAdj extends Model
{
    protected $fillable = [
        'ffne_id',
        'qty',
        'type',
        'notes',
    ];

    /**
     * Get the FFNE associated with this stock adjustment.
     */
    public function ffne()
    {
        return $this->belongsTo(Ffne::class, 'ffne_id');
    }
}
