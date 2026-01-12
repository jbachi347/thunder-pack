<?php

namespace ThunderPack\Models;

use Illuminate\Database\Eloquent\Model;

class AvailableLimit extends Model
{
    protected $fillable = [
        'key',
        'name',
        'category',
        'description',
        'default_value',
        'unit',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_value' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope to only active limits
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get formatted display name with unit
     */
    public function getDisplayNameAttribute()
    {
        if ($this->unit) {
            return $this->name . ' (' . $this->unit . ')';
        }
        return $this->name;
    }
}