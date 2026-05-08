<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Tous les rôles atterrissent sur /dashboard
        // Le DashboardController se charge de rediriger vers la bonne vue selon le rôle
        // return redirect()->intended(route('dashboard'));
        $user = Auth::user();

        // dd($user);

        $roleName = $user->role->name ?? null;
        // dd($roleName);

        if ($roleName === 'Admin') {
            $redirect = route('reporting.index');
        } elseif ($roleName === 'CP') {
            $redirect = route('reporting.index');
        } elseif ($roleName === 'SUP') {
            $redirect = route('reporting.index');
        } elseif ($roleName === 'TC') {
            $redirect = route('reporting.index');
        }

        return redirect()->route('reporting.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
