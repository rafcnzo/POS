<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Sale extends Model
{
    protected $fillable = [
        'transaction_code', 'user_id', 'customer_name', 'table_number', 'order_type',
        'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'status', 'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
