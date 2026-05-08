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
        if ($user->isAdmin()) {
        // Admin : toutes les campagnes avec compteurs
        $campaigns = Campaign::withCount([
            'assignments as cp_count' => fn($q) => $q->whereHas('position', fn($q) => $q->where('code', 'CP')),
            'assignments as sup_count' => fn($q) => $q->whereHas('position', fn($q) => $q->where('code', 'SUP')),
            'assignments as tc_count' => fn($q) => $q->whereHas('position', fn($q) => $q->where('code', 'TC')),
        ])->get();
    } else {
        // Autres rôles : uniquement les campagnes où ils sont affectés
        $employee = $user->employee;

        if (!$employee) {
            $campaigns = collect();
        } else {
            $campaignIds = $employee->assignments()
                ->where('status', 'actif')
                ->pluck('campaign_id');

            $campaigns = Campaign::whereIn('id', $campaignIds)
                ->withCount([
                    'assignments as cp_count' => fn($q) => $q->whereHas('position', fn($q) => $q->where('code', 'CP')),
                    'assignments as sup_count' => fn($q) => $q->whereHas('position', fn($q) => $q->where('code', 'SUP')),
                    'assignments as tc_count' => fn($q) => $q->whereHas('position', fn($q) => $q->where('code', 'TC')),
                ])->get();
        }
    }

    return Inertia::render('Campaigns/Campaign', [
        'campaigns'  => $campaigns,
        'isAdmin'    => $user->isAdmin(),
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
       $user = Auth::user();

    // Non-admin : vérifier qu'il est bien affecté à cette campagne
    if (!$user->isAdmin()) {
        $employee = $user->employee;
        $hasAccess = $employee?->assignments()
            ->where('campaign_id', $id)
            ->where('status', 'actif')
            ->exists();

        if (!$hasAccess) {
            abort(403, "Vous n'avez pas accès à cette campagne.");
        }
    }

    $campaign = Campaign::with([
        'assignments' => fn($q) => $q->where('status', 'actif')
            ->with(['employee.user', 'position'])
    ])->findOrFail($id);

    $assignments = $campaign->assignments;

    $summary = [
        'total_resources' => $assignments->count(),
        'cp_count'  => $assignments->filter(fn($a) => optional($a->position)->code === 'CP')->count(),
        'sup_count' => $assignments->filter(fn($a) => optional($a->position)->code === 'SUP')->count(),
        'tc_count'  => $assignments->filter(fn($a) => optional($a->position)->code === 'TC')->count(),
    ];

    $assignments->each(fn($a) => $a->setAttribute('tree_children', []));
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
        'campaign'  => $campaign,
        'summary'   => $summary,
        'hierarchy' => $hierarchy,
        'isAdmin'   => $user->isAdmin(),
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
