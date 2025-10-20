<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Karyawan extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    /**
     * Relasi ke riwayat penggajian
     */
    public function payroll()
    {
        return $this->hasMany(Payroll::class);
    }
}
