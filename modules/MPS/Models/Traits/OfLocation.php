<?php

namespace Modules\MPS\Models\Traits;

trait OfLocation
{
    public static function scopeOfLocation($query)
    {
        return $query->where('location_id', session('location_id', null));
        // if ($location_id = session('location_id')) {
        //     return $query->where('location_id', $location_id);
        // }
        // return $query;
    }
}
