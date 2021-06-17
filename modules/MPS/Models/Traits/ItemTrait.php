<?php

namespace Modules\MPS\Models\Traits;

use Carbon\Carbon;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\Variation;
use Illuminate\Support\Facades\DB;
use Modules\MPS\Models\StockTrail;

trait ItemTrait
{
    public function allRelation()
    {
        return $this->loadMissing([
            'variations', 'modifiers', 'portions', 'unit.subunits', 'serials',
            'locationStock', 'validPromotions', 'categories.validPromotions',
            'categories:id,name', 'taxes', 'stock:item_id,location_id,quantity',
        ]);
    }

    public function getCurrentStock()
    {
        return $this->getStockOn(Carbon::now());
    }

    public function getStockOn(Carbon $date, $location = null)
    {
        return $this->locationStockTrails($location)->before($date)->sum('quantity');
    }

    public function listRelation()
    {
        return $this->loadMissing([
            'stock:item_id,location_id,quantity', 'locationStock',
            'categories:id,name', 'taxes:id,name', 'unit.subunits',
        ]);
    }

    public function locationStock($location = null)
    {
        return $this->stock()->ofLocation($location);
    }

    public function locationStockTrails($location = null)
    {
        return $this->stockTrails()->ofLocation($location);
    }

    public function purchaseRelation()
    {
        return $this->loadMissing([
            'variations', 'unit.subunits', 'taxes', 'locationStock',
            'categories:id,name', 'stock:item_id,location_id,quantity',
        ]);
    }

    public function saveItem($data, $update = false)
    {
        $item = $this;
        return DB::transaction(function () use ($data, $item, $update) {
            if ($update) {
                $updated = $item->update($data);
                $item->serials()->initial()->delete();
            } else {
                $item = Item::create($data);
            }
            $item->setStock($data['stock'] ?? [], $data['cost']);
            $item->taxes()->sync($data['taxes'] ?? []);
            $item->setPortions($data['portions'] ?? []);
            $item->categories()->sync($data['category_id']);
            $item->setVariations($data['variations'] ?? []);
            $item->modifiers()->sync($data['modifiers'] ?? []);
            // $item->ingredients()->sync($data['ingredients'] ?? []);
            if (!empty($data['unit']) && !empty($data['unit']['subunits'])) {
                foreach ($data['unit']['subunits'] as $unit) {
                    if ($unit['cost'] || $unit['price']) {
                        $item->unitPrice()->create([
                            'unit_id' => $unit['id'],
                            'cost'    => $unit['cost'],
                            'price'   => $unit['price'],
                        ]);
                    }
                }
            }
            if (!empty($data['serials'])) {
                foreach ($data['serials'] as $serial) {
                    if ($serial['number'] && empty($serial['till'])) {
                        $item->serials()->create(['number' => $serial['number']]);
                    } elseif ($serial['number'] && !empty($serial['till'])) {
                        for ($i = $serial['number']; $i <= $serial['till']; $i++) {
                            $item->serials()->create(['number' => $i]);
                        }
                    }
                }
                // $item->serials()->createMany($data['serials']);
            }
            return $update ? $updated : $item;
        });
    }

    public function scopeLoadAll()
    {
        return $this->loadMissing([
            'variations', 'modifiers', 'portions', 'unit.subunits', 'serials',
            'locationStock', 'validPromotions', 'categories.validPromotions',
            'categories:id,name', 'taxes', 'stock:item_id,location_id,quantity',
        ]);
    }

    public static function scopeOfType($query, $type)
    {
        if (is_array($type)) {
            $r = 0;
            foreach ($type as $value) {
                if (!$r) {
                    $query->where('type', $value);
                } else {
                    $query->orWhere('type', $value);
                }
                $r++;
            }
            return $query;
        }
        return $query->where('type', $type);
    }

    public static function scopePos($query)
    {
        return $query->whereNull('hide_in_pos')->orWhere('hide_in_pos', 0);
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('code', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
                // ->orWhere('alt_name', 'LIKE', "%$search%");
            });
        }
        return $query;
    }

    public static function scopeShop($query)
    {
        return $query->whereNull('hide_in_shop')->orWhere('hide_in_shop', 0);
    }

    public function scopeWithAdjustment($query)
    {
        return $query->with([
            'variations', 'unit.subunits', 'taxes', 'locationStock',
            'categories:id,name', 'stock:item_id,location_id,quantity',
        ]);
    }

    public function scopeWithAll($query)
    {
        return $query->with([
            'variations', 'modifiers', 'portions', 'unit.subunits', 'serials',
            'locationStock', 'validPromotions', 'categories.validPromotions',
            'categories:id,name', 'taxes', 'stock:item_id,location_id,quantity',
        ]);
    }

    public function scopeWithDelivery($query)
    {
        return $query->with([
            'variations', 'modifiers', 'portions', 'unit', 'serials',
        ]);
    }

    public function scopeWithList($query)
    {
        return $query->with([
            'stock:item_id,location_id,quantity', 'locationStock',
            'categories:id,name', 'taxes:id,name', 'unit.subunits',
        ]);
    }

    public function scopeWithPurchase($query)
    {
        return $query->with([
            'variations', 'unit.subunits', 'taxes', 'locationStock',
            'categories:id,name', 'stock:item_id,location_id,quantity',
        ]);
    }

    public function scopeWithTransfer($query, $location)
    {
        return $query->with([
            'variations', 'unit.subunits', 'serials',
            'stock' => fn ($q) => $q->ofLocation($location),
            'categories:id,name', 'stock:item_id,location_id,quantity',
            'variations.stock' => fn ($q) => $q->ofLocation($location),
        ]);
    }

    public function setPortions($portions)
    {
        if (isset($portions) && !empty($portions)) {
            $this->portions->each->delete();
            foreach ($portions as $portion) {
                if ($portion['sku'] && $portion['name'] && $portion['cost'] && $portion['name'] && (isset($portion['portion_items']) || isset($portion['essentials']) || isset($portion['choosables']))) {
                    $instance = $this->portions()->create($portion);
                    if (isset($portion['portion_items']) && !empty($portion['portion_items'])) {
                        foreach ($portion['portion_items'] as $portionItem) {
                            unset($portionItem['portion_id']);
                            $instance->portionItems()->updateOrCreate(['item_id' => $portionItem['item_id']], $portionItem);
                        }
                    }
                    if (isset($portion['essentials']) && !empty($portion['essentials'])) {
                        foreach ($portion['essentials'] as $item) {
                            if (isset($item['id']) && !empty($item['id']) && isset($item['quantity']) && !empty($item['quantity'])) {
                                $instance->essentials()->create($item);
                            }
                        }
                    }
                    if (isset($portion['choosables']) && !empty($portion['choosables'])) {
                        foreach ($portion['choosables'] as $group) {
                            if (isset($group['name']) && !empty($group['name'])) {
                                $choosable = $instance->choosables()->create($group);
                                foreach ($group['items'] as $gItem) {
                                    if (isset($gItem['id']) && !empty($gItem['id']) && isset($gItem['quantity']) && !empty($gItem['quantity'])) {
                                        $choosable->items()->updateOrCreate(['item_id' => $gItem['id']], $gItem);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function setStock($locations, $cost)
    {
        foreach ($locations as $location) {
            $location['cost']     ??= null;
            $location['rack']     ??= null;
            $location['price']    ??= null;
            $location['quantity'] ??= null;
            $location['avg_cost'] ??= $cost;
            if ($location['cost'] || $location['price'] || $location['quantity'] || isset($location['rack'])) {
                $stock = $this->stock()->where('location_id', $location['location_id'])->first();
                if ($stock) {
                    $stock->update(['cost' => $location['cost'], 'price' => $location['price'], 'rack' => $location['rack'], 'quantity' => $location['quantity']]);
                } else {
                    $stock = $this->stock()->create([
                        'cost'        => $location['cost'],
                        'rack'        => $location['rack'],
                        'price'       => $location['price'],
                        'quantity'    => $location['quantity'],
                        'location_id' => $location['location_id'],
                        'avg_cost'    => isset($location['cost']) && !empty($location['cost']) ? $location['cost'] : $cost,
                    ]);
                    // $this->stockTrails()->create([
                    //     'quantity'    => $location['quantity'],
                    //     'location_id' => $location['location_id'],
                    // ]);
                }
                if (!empty($location['units'])) {
                    foreach ($location['units'] as $unit) {
                        if ($unit['cost'] || $unit['price']) {
                            $stock->unitPrice()->create([
                                'unit_id' => $unit['id'],
                                'cost'    => $unit['cost'],
                                'price'   => $unit['price'],
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function setVariations($variations)
    {
        foreach ($variations as $variation) {
            if (isset($variation['meta']) && !empty($variation['meta'])) {
                // $this->variations()->updateOrCreate(['sku' => $variation['sku']], $variation);
                $instance = Variation::where('sku', $variation['sku'])->first();
                if ($instance) {
                    $instance->update($variation);
                } else {
                    $variation['item_id'] = $this->id;
                    $instance             = Variation::create($variation);
                }
                if (!empty($variation['stock'])) {
                    foreach ($variation['stock'] as $stock) {
                        $stock['avg_cost'] = $stock['cost'];
                        $instance->stock()->updateOrCreate(['location_id' => $stock['location_id']], $stock);
                    }
                }
            }
        }
    }

    public function stockTrails()
    {
        return $this->hasMany(StockTrail::class);
    }

    public function transferRelation($location)
    {
        return $this->loadMissing([
            'variations', 'unit.subunits', 'serials',
            'stock' => fn ($q) => $q->ofLocation($location),
            'categories:id,name', 'stock:item_id,location_id,quantity',
            'variations.stock' => fn ($q) => $q->ofLocation($location),
        ]);
    }
}
