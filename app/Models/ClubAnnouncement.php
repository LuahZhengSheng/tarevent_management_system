<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubAnnouncement extends Model
{
    protected $fillable = [
        'club_id',
        'title',
        'content',
        'image',
        'status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the club that owns the announcement.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the user who created the announcement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at');
    }

    /**
     * Scope a query to only include draft announcements.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Publish the announcement.
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the announcement (set to draft).
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Get the image URL attribute.
     * Handles both correct storage paths and incorrect temporary file paths.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // If image path contains backslashes or starts with C:\, it's an invalid path
        // This happens when temporary file paths were incorrectly saved
        if (strpos($this->image, '\\') !== false || strpos($this->image, 'C:') === 0) {
            return null; // Return null for invalid paths
        }

        // Check if path already starts with storage/ or /storage/
        if (strpos($this->image, 'storage/') === 0 || strpos($this->image, '/storage/') === 0) {
            return asset($this->image);
        }

        // Normal storage path
        return asset('storage/' . $this->image);
    }
}
