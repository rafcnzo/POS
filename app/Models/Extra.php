<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $fillable = ['ffne_id', 'nama', 'harga', 'keterangan', 'tanggal'];

    public function ffnes()
    {
        return $this->hasMany(Ffne::class, 'extra_id');
    }
}
