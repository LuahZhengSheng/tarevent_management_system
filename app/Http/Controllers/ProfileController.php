<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AvatarService;
use App\Services\ProfileRouteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private AvatarService $avatarService,
        private ProfileRouteService $routeService
    ) {}
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view($this->routeService->getEditViewName(), [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // 只更新允许修改的字段（email, student_id, program 不允许修改）
        $user->fill($request->only(['name', 'phone', 'interested_categories']));

        // 处理头像上传（可选）
        if ($request->hasFile('avatar')) {
            $this->avatarService->uploadAvatar($user, $request->file('avatar'));
        } else {
            $user->save();
        }

        return Redirect::route($this->routeService->getEditRouteName())
            ->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
