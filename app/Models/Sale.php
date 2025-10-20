<?php
namespace App\Models;

use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    const TYPE_REGULAR       = 'regular';
    const TYPE_EMPLOYEE      = 'employee_meal';
    const TYPE_COMPLIMENTARY = 'complimentary';

    protected $fillable = [
        'transaction_code', 'user_id', 'type',
        'customer_name', 'table_number', 'order_type',
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
