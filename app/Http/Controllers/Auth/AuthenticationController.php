<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticationController extends Controller
{
    public function showUserLogin(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return $this->redirectAuthenticatedUser($request->user());
        }

        return view('auth.login');
    }

    public function showUserRegister(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return $this->redirectAuthenticatedUser($request->user());
        }

        return view('auth.register');
    }

    public function showAdminLogin(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return $this->redirectAuthenticatedUser($request->user());
        }

        return view('admin.auth.login');
    }

    public function loginUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
            'redirect_to' => ['nullable', 'url'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        $this->ensureValidPassword($user, $validated['password'], 'email', 'Email atau password tidak sesuai.');

        if (! $user->isCustomer()) {
            throw ValidationException::withMessages([
                'email' => 'Akun ini bukan akun pelanggan. Silakan masuk melalui portal admin.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()
            ->intended($validated['redirect_to'] ?? route('landing'))
            ->with('success', 'Selamat datang kembali.');
    }

    public function registerUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'redirect_to' => ['nullable', 'url'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => $validated['password'],
            'role' => UserRole::Customer,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->to($validated['redirect_to'] ?? route('landing'))
            ->with('success', 'Akun pelanggan berhasil dibuat.');
    }

    public function loginAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $login = $validated['login'];
        $user = User::query()
            ->where('email', $login)
            ->orWhere('username', $login)
            ->first();

        $this->ensureValidPassword($user, $validated['password'], 'login', 'Email/username atau password tidak sesuai.');

        if (! $user->isAdmin() && ! $user->isStaff()) {
            throw ValidationException::withMessages([
                'login' => 'Akun ini tidak memiliki akses panel admin.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Berhasil masuk ke panel admin.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $shouldReturnToAdminLogin = str_contains((string) $request->headers->get('referer'), '/admin');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route($shouldReturnToAdminLogin ? 'admin.login' : 'landing')
            ->with('success', 'Kamu sudah keluar dari akun.');
    }

    private function ensureValidPassword(?User $user, string $password, string $field, string $message): void
    {
        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                $field => $message,
            ]);
        }
    }

    private function redirectAuthenticatedUser(User $user): RedirectResponse
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('landing');
    }
}
