<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'parent_id',
        'post_count',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'post_count' => 'integer',
        'order' => 'integer',
    ];

    protected $attributes = [
        'color' => '#6c757d',
        'is_active' => true,
        'post_count' => 0,
        'order' => 0,
    ];

    // =============================
    // Relationships
    // =============================

    /**
     * Category has many posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Parent category
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Child categories
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
    }

    // =============================
    // Scopes
    // =============================

    /**
     * Scope: Only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Only parent categories
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Order by custom order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Scope: With post count
     */
    public function scopeWithPostCount($query)
    {
        return $query->withCount('posts');
    }

    // =============================
    // Accessors & Mutators
    // =============================

    /**
     * Get category icon with default
     */
    public function getIconAttribute($value)
    {
        return $value ?? 'bi-folder';
    }

    // =============================
    // Methods
    // =============================

    /**
     * Increment post count
     */
    public function incrementPostCount()
    {
        $this->increment('post_count');
        
        // Also increment parent if exists
        if ($this->parent) {
            $this->parent->incrementPostCount();
        }
    }

    /**
     * Decrement post count
     */
    public function decrementPostCount()
    {
        if ($this->post_count > 0) {
            $this->decrement('post_count');
            
            // Also decrement parent if exists
            if ($this->parent) {
                $this->parent->decrementPostCount();
            }
        }
    }

    /**
     * Recalculate post count
     */
    public function recalculatePostCount()
    {
        $this->post_count = $this->posts()->where('status', 'published')->count();
        $this->save();
    }

    /**
     * Check if category has children
     */
    public function hasChildren()
    {
        return $this->children()->exists();
    }

    /**
     * Check if this is a parent category
     */
    public function isParent()
    {
        return is_null($this->parent_id);
    }

    /**
     * Get full category path (for breadcrumbs)
     */
    public function getPathAttribute()
    {
        $path = collect([$this]);
        
        $parent = $this->parent;
        while ($parent) {
            $path->prepend($parent);
            $parent = $parent->parent;
        }
        
        return $path;
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // =============================
    // Boot
    // =============================

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                // Ensure uniqueness
                $originalSlug = $category->slug;
                $count = 1;
                while (static::where('slug', $category->slug)->exists()) {
                    $category->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Prevent deletion if has posts
        static::deleting(function ($category) {
            if ($category->posts()->exists()) {
                throw new \Exception('Cannot delete category with existing posts');
            }
        });
    }
}
