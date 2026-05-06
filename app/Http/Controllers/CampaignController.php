<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        $campaign = Campaign::with('employees.user.role')->first();
        // Ceci va afficher le nom du rôle du premier employé de la campagne
        $emp = $campaign->employees->first();

        if (!$emp) {
            dd("Erreur : La campagne n'a aucun employé assigné.");
        }

        if (!$emp->user) {
            dd("Erreur : L'employé (ID: {$emp->id}) n'a pas de 'user_id' valide ou la relation user() est mal définie.");
        }

        if (!$emp->user->role) {
            dd("Erreur : L'utilisateur (ID: {$emp->user->id}) n'a aucun 'role_id' assigné dans la table users.");
        }

        dd("Tout est bon, le rôle est : " . $emp->user->role->name);
        $user = Auth::user();
        // 1. Validation du rôle Admin
        if (!$user || !$user->isAdmin()) {
            abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        }

        // 2. Récupération des campagnes avec compteurs
        $campaigns = Campaign::withCount([
            // Compte les CP
            'employees as cp_count' => function ($query) {
                $query->whereHas('user.role', function ($q) {
                    $q->where('name', 'CP'); // Assure-toi que le nom en base est 'CP'
                });
            },
            // Compte les SUP
            'employees as sup_count' => function ($query) {
                $query->whereHas('user.role', function ($q) {
                    $q->where('name', 'SUP');
                });
            },
            // Compte les TC
            'employees as tc_count' => function ($query) {
                $query->whereHas('user.role', function ($q) {
                    $q->where('name', 'TC');
                });
            }
        ])->get();

        return Inertia::render('Campaigns/Campaign', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
