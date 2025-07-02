<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateCounty extends Model
{
    protected $table = 'states_counties';

    protected $fillable = [
        'country_code',
        'code',
        'name',
        'type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCountry($query, $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
