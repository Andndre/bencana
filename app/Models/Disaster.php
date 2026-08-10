<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperDisaster
 */
class Disaster extends Model
{
    /** Warna marker peta per slug bencana. Hue sengaja dijauhkan (>40°) supaya mudah dibedakan. */
    public const COLORS = [
        'banjir' => '#2563eb',              // biru
        'tsunami' => '#14b8a6',             // teal
        'gempa-bumi' => '#dc2626',          // merah
        'tanah-longsor' => '#a16207',       // cokelat
        'angin-puting-beliung' => '#7c3aed', // ungu
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
