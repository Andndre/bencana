<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperDisaster
 */
class Disaster extends Model
{
    /** Warna marker peta per slug bencana. */
    public const COLORS = [
        'banjir' => '#1e40af',
        'tanah-longsor' => '#78350f',
        'gempa-bumi' => '#991b1b',
        'tsunami' => '#0c4a6e',
        'angin-puting-beliung' => '#065f46',
    ];

    public const DEFAULT_COLOR = '#800000';

    protected $fillable = ['slug', 'name', 'description'];

    public function getColorAttribute(): string
    {
        return self::COLORS[$this->slug] ?? self::DEFAULT_COLOR;
    }

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
