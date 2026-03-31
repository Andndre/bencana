<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MitigationStep extends Model
{
    protected $fillable = ['disaster_id', 'phase', 'order', 'content'];

    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class);
    }
}
