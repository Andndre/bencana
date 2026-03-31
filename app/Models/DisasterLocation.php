<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisasterLocation extends Model
{
    protected $fillable = ['disaster_id', 'location_name', 'latitude', 'longitude'];

    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }
}
