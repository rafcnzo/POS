<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'stock',
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

    public function storeRequestItems()
    {
        return $this->morphMany(StoreRequestItem::class, 'itemable');
    }

    public function purchaseOrderItems()
    {
        return $this->morphMany(PurchaseOrderItem::class, 'itemable');
    }

    public function getAverageCost(): float
    {
        // Query ini sama persis dengan di Ingredient
        $result = $this->purchaseOrderItems() 
            ->select(DB::raw('SUM(price * quantity) as total_value, SUM(quantity) as total_quantity'))
            ->first();
            
        if (!$result || $result->total_quantity == 0) {
            // Fallback ke 'harga' jika belum pernah dibeli
            return (float) $this->harga; 
        }

        $averageCost = $result->total_value / $result->total_quantity;
        return (float) $averageCost;
    }
}
