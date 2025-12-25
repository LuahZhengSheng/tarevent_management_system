<?php
/**
 * Author: Tang Lit Xuan
 */
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Services\ProgramOptionsProvider;
use App\Services\UserService;
use App\Services\AvatarService;
use App\Services\Strategies\StudentRegistrationStrategy;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private ProgramOptionsProvider $programProvider,
        private UserService $userService,
        private AvatarService $avatarService
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $programOptions = $this->programProvider->getOptions();
        return view('auth.register', compact('programOptions'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterUserRequest $request): RedirectResponse
    {
        // Create user using UserService with StudentRegistrationStrategy
        // Strategy handles: no email sent, no auto-verification, user provides password
        $data = $request->validated();
        $strategy = new StudentRegistrationStrategy();
        $user = $this->userService->createUser($data, $strategy);

        // Handle avatar upload (optional) using AvatarService
        if ($request->hasFile('avatar')) {
            $this->avatarService->uploadAvatar($user, $request->file('avatar'));
        }

        event(new Registered($user));

        // Do not auto-login. User must verify email first, then login manually.
        // Redirect to login page with success message
        return redirect()->route('login')
            ->with('status', 'Registration successful! Please check your email to verify your account before logging in.');
    }
}
