<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminForumTagController extends Controller {

    public function index(Request $request) {
        // page shell; table loaded via include or ajax
        return view('admin.forums.tags.index');
    }

    public function table(Request $request) {
        $query = Tag::query()->with('creator', 'approver', 'mergedInto');

        // Search
        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Status filter
        $status = $request->get('status', 'all');
        if ($status !== 'all' && in_array($status, ['active', 'pending', 'banned', 'merged'], true)) {
            $query->where('status', $status);
        }

        // Type filter
        $type = $request->get('type', 'all');
        if ($type !== 'all' && in_array($type, ['official', 'community'], true)) {
            $query->where('type', $type);
        }

        // ✅ 默认排序：pending 在最前，然后按用户选的 sortBy
        $sortBy = $request->get('sortby', 'usage');

        // 1) 先按 status 排序（pending=1, 其它=2）
        $query->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END");

        // 2) 再按用户选的排序
        if ($sortBy === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', 'asc');
        } else { // usage (default)
            $query->orderBy('usage_count', 'desc')->orderBy('name', 'asc');
        }

        $tags = $query->paginate(15)->withQueryString();

        if ($request->ajax() || $request->boolean('ajax')) {
            $html = view('admin.forums.tags.partials.table', compact('tags'))->render();
            $pagination = view('admin.users.partials.pagination', ['users' => $tags])->render();

            return response()->json([
                        'success' => true,
                        'html' => $html,
                        'pagination' => $pagination,
            ]);
        }

        return view('admin.forums.tags.partials.table', compact('tags'));
    }

    public function approve(Tag $tag) {
        if ($tag->status !== 'pending') {
            return response()->json([
                        'success' => false,
                        'message' => 'Only pending tags can be approved.',
                            ], 422);
        }

        $tag->approve(auth()->id());

        return response()->json([
                    'success' => true,
                    'message' => "Tag \"{$tag->name}\" approved successfully.",
        ]);
    }

    public function reject(Tag $tag) {
        if ($tag->status !== 'pending') {
            return response()->json([
                        'success' => false,
                        'message' => 'Only pending tags can be rejected.',
                            ], 422);
        }

        $tag->reject();

        return response()->json([
                    'success' => true,
                    'message' => "Tag \"{$tag->name}\" rejected (banned) successfully.",
        ]);
    }

    public function update(Request $request, Tag $tag) {
        $validated = $request->validate([
            'name' => 'nullable|string|min:2|max:50',
            'status' => 'nullable|in:inactive,banned,pending,merged',
        ]);

        if (array_key_exists('name', $validated) && $validated['name'] !== null) {
            $name = strtolower(trim($validated['name']));
            $name = preg_replace('/\s+/', ' ', $name);
            $tag->name = $name;

            // Tag model boot 会自动更新 slug，但你要确保唯一性
            $newSlug = \Illuminate\Support\Str::slug($name);
            $exists = Tag::where('slug', $newSlug)
                    ->where('id', '!=', $tag->id)
                    ->exists();

            if ($exists) {
                return response()->json([
                            'success' => false,
                            'message' => 'Slug conflict. Choose another name.',
                                ], 422);
            }
        }

        if (array_key_exists('status', $validated) && $validated['status'] !== null) {
            $tag->status = $validated['status'];
        }

        $tag->save();

        return response()->json([
                    'success' => true,
                    'message' => 'Tag updated successfully.',
        ]);
    }
}
