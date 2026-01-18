<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staffing extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'order',
        'synced_to_vatsim',
        'synced_at',
    ];

    protected $casts = [
        'synced_to_vatsim' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(StaffingPosition::class)->orderBy('order');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function isSynced(): bool
    {
        return $this->synced_to_vatsim;
    }
}
