<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Backwards-compatible alias for older code paths that used
     * BusinessSubCategory->primaryCategory().
     */
    public function primaryCategory()
    {
        return $this->parent();
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('display_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

