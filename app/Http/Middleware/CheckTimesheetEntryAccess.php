<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTimesheetEntryAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $roleName = strtoupper($user->role?->name ?? '');

        // Si l'utilisateur a un des rôles autorisés
        foreach ($roles as $role) {
            if (strtoupper($role) === $roleName) {
                return $next($request);
            }
        }

        // Redirection avec message d'erreur pour les autres rôles
        return redirect()->route('dashboard')
            ->with('error', 'Vous n\'avez pas les droits nécessaires pour accéder à cette section.');
    
    }
}
