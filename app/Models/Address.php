<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    protected $fillable = [
        'type',
        'name',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [];
    }

    /**
     * Get the parent address (street's ward).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'parent_id');
    }

    /**
     * Get child addresses (ward's streets).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Address::class, 'parent_id')->orderBy('name');
    }


    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by parent.
     */
    public function scopeOfParent($query, ?int $parentId)
    {
        if ($parentId === null) {
            return $query->whereNull('parent_id');
        }
        return $query->where('parent_id', $parentId);
    }

    /**
     * Get full path of the address (e.g., "Phường Cống Vị > Đường ABC").
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }
}
