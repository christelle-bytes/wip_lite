<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function index()
    {
        // Positions
        $cpPosition    = Position::where('code', 'CP')->first();
        $supPosition   = Position::where('code', 'SUP')->first();
        $tcPosition    = Position::where('code', 'TC')->first();

        // Campagnes actives
        $activeCampaigns = Campaign::where('status', 'active')->get();

        // CP non affectés (aucun assignment actif)
        $unassignedCPs = Employee::where('position_id', $cpPosition->id)
            ->where('status', 'actif')
            ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
            ->with('user')
            ->get();

        // SUP non affectés
        $unassignedSUPs = Employee::where('position_id', $supPosition->id)
            ->where('status', 'actif')
            ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
            ->with('user')
            ->get();

        // TC non affectés
        $unassignedTCs = Employee::where('position_id', $tcPosition->id)
            ->where('status', 'actif')
            ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
            ->with('user')
            ->get();

        // CP déjà affectés (pour affecter un SUP sous un CP)
        $assignedCPs = Assignment::where('position_id', $cpPosition->id)
            ->where('status', 'actif')
            ->with(['employee.user', 'campaign'])
            ->get();

        // SUP déjà affectés (pour affecter un TC sous un SUP)
        $assignedSUPs = Assignment::where('position_id', $supPosition->id)
            ->where('status', 'actif')
            ->with(['employee.user', 'campaign'])
            ->get();

        // Vue arborescente : toutes les campagnes avec leur hiérarchie
        $campaigns = Campaign::with([
            'assignments' => fn($q) => $q->where('status', 'actif')
                ->with(['employee.user', 'position']),
        ])->get()->map(function ($campaign) use ($cpPosition, $supPosition, $tcPosition) {

            $assignments = $campaign->assignments;

            $cps = $assignments->where('position_id', $cpPosition->id)->values();
            $sups = $assignments->where('position_id', $supPosition->id)->values();
            $tcs = $assignments->where('position_id', $tcPosition->id)->values();

            // Construire la hiérarchie CP > SUP > TC
            $tree = $cps->map(function ($cp) use ($sups, $tcs) {
                $cpSups = $sups->where('manager_id', $cp->employee_id)->values();
                $cpSups = $cpSups->map(function ($sup) use ($tcs) {
                    $supTcs = $tcs->where('manager_id', $sup->employee_id)->values();
                    return array_merge($sup->toArray(), ['children' => $supTcs->toArray()]);
                });
                return array_merge($cp->toArray(), ['children' => $cpSups->toArray()]);
            });

            return [
                'id'     => $campaign->id,
                'name'   => $campaign->name,
                'status' => $campaign->status,
                'tree'   => $tree,
            ];
        });

        return Inertia::render('Assignments/Index', [
            'activeCampaigns' => $activeCampaigns,
            'unassignedCPs'   => $unassignedCPs,
            'unassignedSUPs'  => $unassignedSUPs,
            'unassignedTCs'   => $unassignedTCs,
            'assignedCPs'     => $assignedCPs,
            'assignedSUPs'    => $assignedSUPs,
            'campaigns'       => $campaigns,
        ]);
    }

    // ─── Affecter un CP à une campagne ────────────────────────────────────────
    public function assignCP(Request $request)
    {
        $data = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'campaign_ids' => 'required|array|min:1',
            'campaign_ids.*' => 'exists:campaigns,id',
            'start_date'   => 'required|date',
        ]);

        $cpPosition = Position::where('code', 'CP')->firstOrFail();

        // Vérifier que toutes les campagnes sélectionnées sont actives
        $campaigns = Campaign::whereIn('id', $data['campaign_ids'])->get();
        foreach ($campaigns as $campaign) {
            if ($campaign->status !== 'active') {
                return back()->withErrors(['campaign_ids' => "La campagne « {$campaign->name} » n'est pas active."]);
            }
            // Éviter les doublons
            $exists = Assignment::where('employee_id', $data['employee_id'])
                ->where('campaign_id', $campaign->id)
                ->where('status', 'actif')
                ->exists();
            if ($exists) {
                return back()->withErrors(['employee_id' => "Ce CP est déjà affecté à la campagne « {$campaign->name} »."]);
            }
        }

        foreach ($data['campaign_ids'] as $campaignId) {
            Assignment::create([
                'employee_id' => $data['employee_id'],
                'campaign_id' => $campaignId,
                'position_id' => $cpPosition->id,
                'manager_id'  => null,
                'status'      => 'actif',
                'start_date'  => $data['start_date'],
            ]);
        }

        return back()->with('success', 'Chef de Plateau affecté avec succès.');
    }

    // ─── Affecter un SUP à un CP/campagne ─────────────────────────────────────
    public function assignSUP(Request $request)
    {
        $data = $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'cp_assignment_id' => 'required|exists:assignments,id',
            'start_date'     => 'required|date',
        ]);

        $supPosition = Position::where('code', 'SUP')->firstOrFail();
        $cpAssignment = Assignment::findOrFail($data['cp_assignment_id']);

        // Règle : un SUP ne peut être affecté qu'à une seule campagne à la fois
        $alreadyAssigned = Assignment::where('employee_id', $data['employee_id'])
            ->where('status', 'actif')
            ->exists();
        if ($alreadyAssigned) {
            return back()->withErrors(['employee_id' => 'Ce Superviseur est déjà affecté à une campagne.']);
        }

        Assignment::create([
            'employee_id' => $data['employee_id'],
            'campaign_id' => $cpAssignment->campaign_id,
            'position_id' => $supPosition->id,
            'manager_id'  => $cpAssignment->employee_id,
            'status'      => 'actif',
            'start_date'  => $data['start_date'],
        ]);

        return back()->with('success', 'Superviseur affecté avec succès.');
    }

    // ─── Affecter un ou plusieurs TC à un SUP ─────────────────────────────────
    public function assignTC(Request $request)
    {
        $data = $request->validate([
            'employee_ids'      => 'required|array|min:1',
            'employee_ids.*'    => 'exists:employees,id',
            'sup_assignment_id' => 'required|exists:assignments,id',
            'start_date'        => 'required|date',
        ]);

        $tcPosition = Position::where('code', 'TC')->firstOrFail();
        $supAssignment = Assignment::findOrFail($data['sup_assignment_id']);

        foreach ($data['employee_ids'] as $employeeId) {
            // Règle : un TC ne peut être affecté qu'à un seul SUP à la fois
            $alreadyAssigned = Assignment::where('employee_id', $employeeId)
                ->where('status', 'actif')
                ->exists();
            if ($alreadyAssigned) {
                $emp = Employee::find($employeeId);
                return back()->withErrors(['employee_ids' => "Le TC {$emp->first_name} {$emp->last_name} est déjà affecté."]);
            }

            Assignment::create([
                'employee_id' => $employeeId,
                'campaign_id' => $supAssignment->campaign_id,
                'position_id' => $tcPosition->id,
                'manager_id'  => $supAssignment->employee_id,
                'status'      => 'actif',
                'start_date'  => $data['start_date'],
            ]);
        }

        return back()->with('success', 'Téléconseiller(s) affecté(s) avec succès.');
    }

    // ─── Libérer une ressource (avec cascade) ─────────────────────────────────
    public function release(Assignment $assignment)
    {
        $supPosition = Position::where('code', 'SUP')->first();

        // Si c'est un SUP, libérer ses TC en cascade
        if ($assignment->position_id === $supPosition->id) {
            Assignment::where('manager_id', $assignment->employee_id)
                ->where('campaign_id', $assignment->campaign_id)
                ->where('status', 'actif')
                ->update(['status' => 'terminé', 'end_date' => now()]);
        }

        // Si c'est un CP, libérer ses SUP et leurs TC en cascade
        $cpPosition = Position::where('code', 'CP')->first();
        if ($assignment->position_id === $cpPosition->id) {
            $sups = Assignment::where('manager_id', $assignment->employee_id)
                ->where('campaign_id', $assignment->campaign_id)
                ->where('status', 'actif')
                ->get();

            foreach ($sups as $sup) {
                // Libérer les TC du SUP
                Assignment::where('manager_id', $sup->employee_id)
                    ->where('campaign_id', $assignment->campaign_id)
                    ->where('status', 'actif')
                    ->update(['status' => 'terminé', 'end_date' => now()]);
            }

            // Libérer les SUP
            Assignment::where('manager_id', $assignment->employee_id)
                ->where('campaign_id', $assignment->campaign_id)
                ->where('status', 'actif')
                ->update(['status' => 'terminé', 'end_date' => now()]);
        }

        $assignment->update(['status' => 'terminé', 'end_date' => now()]);

        return back()->with('success', 'Ressource libérée avec succès.');
    }
}