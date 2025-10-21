<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreRequest extends Model
{
    protected $fillable = ['request_number', 'issued_by', 'issued_at', 'remarks'];

    public function items()
    {
        return $this->hasMany(StoreRequestItem::class)
        ->with('itemable');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
