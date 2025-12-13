<?php
// app/Models/Tag.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'usage_count',
        'merged_into_tag_id',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected $attributes = [
        'type' => 'community',
        'status' => 'pending',
        'usage_count' => 0,
    ];

    // =============================
    // Relationships
    // =============================

    /**
     * Tag has many posts (through pivot)
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag')
                    ->withPivot(['tagged_by', 'source', 'order', 'is_confirmed'])
                    ->withTimestamps();
    }

    /**
     * Tag created by user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Tag approved by admin
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Tag merged into another tag
     */
    public function mergedInto()
    {
        return $this->belongsTo(Tag::class, 'merged_into_tag_id');
    }

    // =============================
    // Scopes
    // =============================

    /**
     * Scope: Only active tags
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Only approved tags (active status)
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Only pending tags
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Official tags
     */
    public function scopeOfficial($query)
    {
        return $query->where('type', 'official');
    }

    /**
     * Scope: Community tags
     */
    public function scopeCommunity($query)
    {
        return $query->where('type', 'community');
    }

    /**
     * Scope: Popular tags
     */
    public function scopePopular($query, $limit = 20)
    {
        return $query->where('status', 'active')
                     ->orderBy('usage_count', 'desc')
                     ->limit($limit);
    }

    /**
     * Scope: Search by name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('slug', 'like', "%{$search}%");
    }

    /**
     * Scope: Usable tags (active or pending)
     */
    public function scopeUsable($query)
    {
        return $query->whereIn('status', ['active', 'pending']);
    }

    // =============================
    // Methods
    // =============================

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
        
        // Auto-approve if usage >= 5 and still pending
        if ($this->status === 'pending' && $this->usage_count >= 5) {
            $this->approve();
        }
    }

    /**
     * Decrement usage count
     */
    public function decrementUsage()
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }

    /**
     * Recalculate usage count
     */
    public function recalculateUsageCount()
    {
        $this->usage_count = $this->posts()->count();
        $this->save();
    }

    /**
     * Approve tag
     */
    public function approve($userId = null)
    {
        $this->update([
            'status' => 'active',
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject/Ban tag
     */
    public function reject()
    {
        $this->update(['status' => 'banned']);
    }

    /**
     * Merge this tag into another
     */
    public function mergeInto(Tag $targetTag)
    {
        DB::transaction(function () use ($targetTag) {
            // Get all post_tag relationships
            $pivotRecords = DB::table('post_tag')
                ->where('tag_id', $this->id)
                ->get();

            // Update or create relationships with target tag
            foreach ($pivotRecords as $record) {
                DB::table('post_tag')->updateOrInsert(
                    [
                        'post_id' => $record->post_id,
                        'tag_id' => $targetTag->id,
                    ],
                    [
                        'tagged_by' => $record->tagged_by,
                        'source' => $record->source,
                        'order' => $record->order,
                        'is_confirmed' => $record->is_confirmed,
                        'updated_at' => now(),
                    ]
                );
            }

            // Delete old relationships
            DB::table('post_tag')->where('tag_id', $this->id)->delete();

            // Update counts
            $targetTag->increment('usage_count', $this->usage_count);

            // Mark as merged
            $this->update([
                'status' => 'merged',
                'merged_into_tag_id' => $targetTag->id,
                'usage_count' => 0,
            ]);
        });
    }

    /**
     * Check if tag is usable
     */
    public function isUsable()
    {
        return in_array($this->status, ['active', 'pending']);
    }

    /**
     * Check if tag is official
     */
    public function isOfficial()
    {
        return $this->type === 'official';
    }

    /**
     * Check if tag needs approval
     */
    public function needsApproval()
    {
        return $this->status === 'pending';
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
        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
                
                // Ensure uniqueness
                $originalSlug = $tag->slug;
                $count = 1;
                while (static::where('slug', $tag->slug)->exists()) {
                    $tag->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name')) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        // Prevent deletion if in use
        static::deleting(function ($tag) {
            if ($tag->posts()->exists()) {
                throw new \Exception('Cannot delete tag that is in use. Consider merging it instead.');
            }
        });
    }
}
