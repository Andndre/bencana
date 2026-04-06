<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArMarker extends Model
{
    public $timestamps = true;

    protected $table = 'ar_marker';

    protected $primaryKey = 'marker_id';

    protected $fillable = [
        'disaster_id',
        'nama',
        'path_gambar_marker',
        'path_patt',
        'path_model',
        'path_audio',
    ];

    protected $casts = [
        'disaster_id' => 'integer',
    ];

    public function disaster(): BelongsTo
    {
        return $this->belongsTo(Disaster::class, 'disaster_id', 'id');
    }
}
