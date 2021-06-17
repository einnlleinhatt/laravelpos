<?php

namespace Modules\MPS\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Modules\MPS\Models\Traits\HasTaxes;
use Modules\MPS\Models\Traits\ItemTrait;
use Modules\MPS\Models\Traits\HasCategories;
use Modules\MPS\Models\Traits\HasPromotions;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Item extends Base
{
    use HasCategories;
    use HasPromotions;
    use HasSchemalessAttributes;
    use HasSlug;
    use HasTaxes;
    use ItemTrait;

    public $hasSKU = true;

    public static $searchable = [
        'id', 'created_at', 'categories.name', 'taxes.name', 'sku',
        'variants', 'has_variants', 'supplier_id', 'supplier_item_id', 'weight', 'dimensions',
        'code', 'name', 'slug', 'photo', 'details', 'summary', 'alt_name', 'rack', 'min_price', 'max_price', 'expiry',
        'changeable', 'type', 'tax_included', 'cost', 'max_discount', 'price', 'symbology', 'is_stock', 'extra_attributes',
        'hide_in_pos', 'hide_in_shop', 'on_sale', 'start_date', 'end_date', 'has_serials', 'brand_id', 'unit_id', 'sale_unit_id', 'purchase_unit_id',
    ];

    protected $casts = ['extra_attributes' => 'array', 'variants' => 'array'];

    protected $fillable = [
        'variants', 'has_variants', 'supplier_id', 'supplier_item_id', 'weight', 'dimensions', 'sku',
        'code', 'name', 'slug', 'photo', 'details', 'summary', 'alt_name', 'rack', 'min_price', 'max_price', 'expiry',
        'changeable', 'type', 'tax_included', 'cost', 'max_discount', 'price', 'symbology', 'is_stock', 'extra_attributes',
        'hide_in_pos', 'hide_in_shop', 'on_sale', 'start_date', 'end_date', 'has_serials', 'brand_id', 'unit_id', 'sale_unit_id', 'purchase_unit_id',
    ];

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($item) {
            $item->stock()->delete();
            $item->taxes()->detach();
            $item->serials()->delete();
            $item->portions()->delete();
            $item->modifiers()->detach();
            $item->categories()->detach();
            // $item->variations()->delete();
            $item->stockTrails()->delete();
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function del()
    {
        if ($this->saleItems()->exists() || $this->purchaseItems()->exists() || $this->modifierOptions()->exists() || $this->portionItem()->exists() || $this->portionEssentialItems()->exists() || $this->portionChoosableItems()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')
            ->saveSlugsTo('slug')->doNotGenerateSlugsOnUpdate();
    }

    public function modifierOptions()
    {
        return $this->hasMany(ModifierOption::class);
    }

    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class);
    }

    public function portionChoosableItems()
    {
        return $this->hasMany(PortionChoosableItem::class);
    }

    public function portionEssentialItems()
    {
        return $this->hasMany(PortionEssential::class);
    }

    public function portionItem()
    {
        return $this->hasMany(PortionItem::class);
    }

    public function portions()
    {
        return $this->hasMany(Portion::class)->orderBy('sku');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function purchases()
    {
        return $this->hasManyThrough(Purchase::class, PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function sales()
    {
        return $this->hasManyThrough(Sale::class, SaleItem::class);
    }

    public function serials()
    {
        return $this->hasMany(Serial::class)->orderBy('number');
    }

    public function stock()
    {
        return $this->hasMany(Stock::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitPrice()
    {
        return $this->morphMany(UnitPrice::class, 'subject');
    }

    public function variations()
    {
        return $this->hasMany(Variation::class)->orderBy('sku');
    }
}
