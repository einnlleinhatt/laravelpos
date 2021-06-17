<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Item;

trait BelongsToItems
{
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
