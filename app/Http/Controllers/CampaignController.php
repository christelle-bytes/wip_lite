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
        // dd(!$user->isAdmin());
        // 1. Validation du rôle Admin
        // if (!$user || !$user->isAdmin()) {
        //     abort(403, "Vous n'avez pas les droits nécessaires pour accéder à cette page.");
        // }

        // 2. Récupération des campagnes avec compteurs
        $campaigns = Campaign::withCount([
            'assignments as cp_count' => function ($query) {
                $query->whereHas('position', function ($q) {
                    $q->where('name', 'Chef Plateau');
                });
            },
            'assignments as sup_count' => function ($query) {
                $query->whereHas('position', function ($q) {
                    $q->where('name', 'Superviseur');
                });
            },
            'assignments as tc_count' => function ($query) {
                $query->whereHas('position', function ($q) {
                    $q->where('name', 'Teleconseiller');
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
        $validated= $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,terminée',
        ]);

        Campaign::create($validated);

        return redirect()->route('campaigns.index')->with('success', 'Campagne créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $campaign = Campaign::with(['assignments.employee.user.role', 'assignments.position'])->findOrFail($id);

        $assignments = $campaign->assignments;

        $summary = [
            'total_resources' => $assignments->count(),
            'cp_count' => $assignments->filter(function ($assignment) {
                return optional($assignment->position)->name === 'Chef Plateau';
            })->count(),
            'sup_count' => $assignments->filter(function ($assignment) {
                return optional($assignment->position)->name === 'Superviseur';
            })->count(),
            'tc_count' => $assignments->filter(function ($assignment) {
                return optional($assignment->position)->name === 'Teleconseiller';
            })->count(),
        ];

        $assignments->each(function ($assignment) {
            $assignment->setAttribute('tree_children', []);
        });

        $assignmentsByEmployee = $assignments->keyBy('employee_id');
        $hierarchy = [];

        foreach ($assignments as $assignment) {
            if ($assignment->manager_id && $assignmentsByEmployee->has($assignment->manager_id)) {
                $manager = $assignmentsByEmployee[$assignment->manager_id];
                $children = $manager->tree_children;
                $children[] = $assignment;
                $manager->setAttribute('tree_children', $children);
            } else {
                $hierarchy[] = $assignment;
            }
        }

        return Inertia::render('Campaigns/Show', [
            'campaign' => $campaign,
            'summary' => $summary,
            'hierarchy' => $hierarchy,
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

       $validated= $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,terminée',
        ]);

        $campaign->update($validated);

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
