<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disaster extends Model
{
    protected $fillable = ['slug', 'name', 'description'];

    public function mitigationSteps(): HasMany
    {
        return $this->hasMany(MitigationStep::class)->orderBy('phase')->orderBy('order');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(DisasterLocation::class);
    }

    public function stepsByPhase(string $phase): HasMany
    {
        return $this->mitigationSteps()->where('phase', $phase)->orderBy('order');
    }
}
