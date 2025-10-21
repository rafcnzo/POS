<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StoreRequestItem extends Model
{
    protected $table = 'store_request_items';
    protected $fillable = ['store_request_id', 'itemable_id', 'itemable_type', 'requested_quantity', 'issued_quantity'];

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function storeRequest()
    {
        return $this->belongsTo(StoreRequest::class);
    }
}
