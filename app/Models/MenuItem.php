<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MenuCategory;
use App\Models\Ingredient;
use App\Models\ModifierGroup;

class MenuItem extends Model
{
    use HasFactory;

    protected $guarded   = []; // Izinkan mass assignment
    public $incrementing = false;
    protected $keyType   = 'string';

    // Ubah relasi ke camelCase agar konsisten dengan controller
    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_menu_item')->withPivot('quantity');
    }

    public function getCostPrice()
    {
        // Asumsi Ingredient punya column 'cost' (biaya per unit)
        return $this->ingredients->sum(fn($i) => $i->cost_price * $i->pivot->quantity);
    }
    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'menu_item_modifier_group');
    }
}
