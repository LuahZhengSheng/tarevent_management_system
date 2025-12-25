<?php

namespace App\Services\Club;

use App\Models\Club;
use App\Models\ClubAnnouncement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * AnnouncementService - Handles club announcement business logic
 * 
 * This service is responsible for:
 * - Creating, updating, and deleting announcements
 * - Publishing and unpublishing announcements
 * - Retrieving announcements for clubs
 */
class AnnouncementService
{
    /**
     * Create a new announcement.
     * 
     * @param Club $club The club
     * @param array $data Announcement data
     * @param User $creator The user creating the announcement
     * @return ClubAnnouncement
     */
    public function create(Club $club, array $data, User $creator): ClubAnnouncement
    {
        return DB::transaction(function () use ($club, $data, $creator) {
            $data['club_id'] = $club->id;
            $data['created_by'] = $creator->id;
            
            // If status is published, set published_at
            if (($data['status'] ?? 'draft') === 'published') {
                $data['published_at'] = now();
            }

            return ClubAnnouncement::create($data);
        });
    }

    /**
     * Update an announcement.
     * 
     * @param ClubAnnouncement $announcement The announcement to update
     * @param array $data Updated data
     * @return ClubAnnouncement
     */
    public function update(ClubAnnouncement $announcement, array $data): ClubAnnouncement
    {
        return DB::transaction(function () use ($announcement, $data) {
            // If status is being changed to published, set published_at
            if (isset($data['status']) && $data['status'] === 'published' && $announcement->status !== 'published') {
                $data['published_at'] = now();
            }
            
            // If status is being changed to draft, clear published_at
            if (isset($data['status']) && $data['status'] === 'draft' && $announcement->status === 'published') {
                $data['published_at'] = null;
            }

            $announcement->update($data);
            return $announcement->fresh();
        });
    }

    /**
     * Delete an announcement.
     * 
     * @param ClubAnnouncement $announcement The announcement to delete
     * @return bool
     */
    public function delete(ClubAnnouncement $announcement): bool
    {
        return DB::transaction(function () use ($announcement) {
            // Delete associated image if exists
            if ($announcement->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($announcement->image);
            }
            
            return $announcement->delete();
        });
    }

    /**
     * Publish an announcement.
     * 
     * @param ClubAnnouncement $announcement The announcement to publish
     * @return ClubAnnouncement
     */
    public function publish(ClubAnnouncement $announcement): ClubAnnouncement
    {
        return DB::transaction(function () use ($announcement) {
            $announcement->publish();
            return $announcement->fresh();
        });
    }

    /**
     * Unpublish an announcement (set to draft).
     * 
     * @param ClubAnnouncement $announcement The announcement to unpublish
     * @return ClubAnnouncement
     */
    public function unpublish(ClubAnnouncement $announcement): ClubAnnouncement
    {
        return DB::transaction(function () use ($announcement) {
            $announcement->unpublish();
            return $announcement->fresh();
        });
    }

    /**
     * Get all announcements for a club.
     * 
     * @param Club $club The club
     * @param array $filters Optional filters (status, limit, etc.)
     * @return Collection
     */
    public function getAnnouncements(Club $club, array $filters = []): Collection
    {
        $query = $club->announcements()->with('creator');

        // Filter by status
        if (isset($filters['status'])) {
            if ($filters['status'] === 'published') {
                $query->published();
            } elseif ($filters['status'] === 'draft') {
                $query->draft();
            }
        }

        // Order by published_at (desc) or created_at (desc)
        $query->orderBy('published_at', 'desc')
              ->orderBy('created_at', 'desc');

        // Limit results
        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get();
    }

    /**
     * Get a single announcement.
     * 
     * @param Club $club The club
     * @param int $announcementId The announcement ID
     * @return ClubAnnouncement|null
     */
    public function getAnnouncement(Club $club, int $announcementId): ?ClubAnnouncement
    {
        return $club->announcements()
            ->with('creator')
            ->find($announcementId);
    }
}

