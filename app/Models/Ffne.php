<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ffne extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kode_ffne',
        'nama_ffne',
        'kategori_ffne',
        'harga',
        'satuan_ffne',
        'kondisi_ffne',
    ];

    /**
     * Get all of the extras for the Ffne
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function extras(): HasMany
    {
        return $this->hasMany(Extra::class, 'ffne_id');
    }
}
