<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroll extends Model
{
    use HasFactory;
    
    protected $table = 'payroll';
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'nominal_gaji' => 'decimal:2',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
