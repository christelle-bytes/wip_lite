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
        return Inertia::render('Campaigns/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,terminée',
        ]);

        Campaign::create($request->all());

        return redirect()->route('campaigns.index')->with('success', 'Campagne créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $campaign = Campaign::with(['assignments.employee', 'assignments.position'])->findOrFail($id);

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $campaign = Campaign::findOrFail($id);

        return Inertia::render('Campaigns/Edit', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $campaign = Campaign::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,terminée',
        ]);

        $campaign->update($request->all());

        return redirect()->route('campaigns.index')->with('success', 'Campagne mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update(['status' => 'inactive']);

        return redirect()->route('campaigns.index')->with('success', 'Campagne désactivée avec succès.');
    }
}
