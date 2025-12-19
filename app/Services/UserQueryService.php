<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserQueryService
{
    /**
     * Build query for listing users (students and clubs)
     */
    public function buildUserListQuery(Request $request): Builder
    {
        $query = User::whereIn('role', ['student', 'club', 'user'])
            ->with(['club']);

        $this->applySearch($query, $request);
        $this->applyRoleFilter($query, $request);
        $this->applyStatusFilter($query, $request);
        $this->applySorting($query, $request);

        return $query;
    }

    /**
     * Build query for listing administrators
     */
    public function buildAdminListQuery(Request $request): Builder
    {
        $query = User::where('role', 'admin')
            ->with([]);

        $this->applySearch($query, $request, ['name', 'email', 'phone']);
        $this->applyStatusFilter($query, $request);
        $this->applySorting($query, $request, ['name', 'email', 'status', 'created_at', 'last_login_at']);

        return $query;
    }

    /**
     * Apply search filter to query
     */
    protected function applySearch(Builder $query, Request $request, array $searchFields = null): void
    {
        if (!$request->filled('search')) {
            return;
        }

        $search = $request->search;
        $fields = $searchFields ?? ['name', 'email', 'student_id', 'phone'];

        $query->where(function ($q) use ($search, $fields) {
            foreach ($fields as $index => $field) {
                if ($index === 0) {
                    $q->where($field, 'like', '%' . $search . '%');
                } else {
                    $q->orWhere($field, 'like', '%' . $search . '%');
                }
            }
        });
    }

    /**
     * Apply role filter to query
     */
    protected function applyRoleFilter(Builder $query, Request $request): void
    {
        if (!$request->filled('role')) {
            return;
        }

        $role = $request->role;
        if ($role === 'student') {
            $query->whereIn('role', ['student', 'user']);
        } elseif ($role === 'club') {
            $query->where('role', 'club');
        }
    }

    /**
     * Apply status filter to query
     */
    protected function applyStatusFilter(Builder $query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting(Builder $query, Request $request, array $allowedSorts = null): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowed = $allowedSorts ?? ['name', 'email', 'role', 'status', 'created_at', 'last_login_at'];

        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Paginate query results
     */
    public function paginate(Builder $query, Request $request, int $defaultPerPage = 15): LengthAwarePaginator
    {
        $perPage = $request->get('per_page', $defaultPerPage);
        return $query->paginate($perPage)->withQueryString();
    }
}

