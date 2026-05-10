<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            // Ne pas rediriger si on est déjà sur la page de changement de mot de passe ou si on se déconnecte
            if (!$request->routeIs('profile.edit') && !$request->routeIs('profile.update') && !$request->routeIs('logout')) {
                return redirect()->route('profile.edit')->with('warning', 'Vous devez changer votre mot de passe par défaut avant de continuer.');
            }
        }

        return $next($request);
    }
}
