<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('role')->paginate(10);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $roles = Role::pluck('name', 'id');

        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}

