<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $employee = $user->employee;

        $cpPosition  = Position::where('code', 'CP')->firstOrFail();
        $supPosition = Position::where('code', 'SUP')->firstOrFail();
        $tcPosition  = Position::where('code', 'TC')->firstOrFail();

        // ── ADMIN : tout voir, tout faire ─────────────────────────────────────
        if ($user->isAdmin()) {
            // Uniquement les campagnes qui n'ont pas encore de CP assigné
            $activeCampaigns = Campaign::where('status', 'active')
                ->whereDoesntHave('assignments', function($q) use ($cpPosition) {
                    $q->where('position_id', $cpPosition->id)->where('status', 'actif');
                })->get();

            $unassignedCPs = Employee::where('position_id', $cpPosition->id)
                ->where('status', 'actif')
                ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
                ->with(['user', 'position'])->get();

            $unassignedSUPs = Employee::where('position_id', $supPosition->id)
                ->where('status', 'actif')
                ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
                ->with(['user', 'position'])->get();

            $unassignedTCs = Employee::where('position_id', $tcPosition->id)
                ->where('status', 'actif')
                ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
                ->with(['user', 'position'])->get();

            $assignedCPs = Assignment::where('position_id', $cpPosition->id)
                ->where('status', 'actif')
                ->with(['employee.user', 'campaign'])->get();

            $assignedSUPs = Assignment::where('position_id', $supPosition->id)
                ->where('status', 'actif')
                ->with(['employee.user', 'campaign'])->get();

            $unassignedEmployees = Employee::where('status', 'actif')
                ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
                ->with([
                    'position', 'user',
                    'assignments' => fn($q) => $q->where('status', 'terminé')
                        ->latest('end_date')->limit(1)->with('campaign'),
                ])
                ->get()
                ->map(function ($emp) {
                    $emp->last_assignment = $emp->assignments->first();
                    unset($emp->assignments);
                    return $emp;
                });

            $campaigns = $this->buildCampaignTree(
                Campaign::all(),
                $cpPosition, $supPosition, $tcPosition
            );

            return Inertia::render('Assignments/Index', [
                'activeCampaigns'     => $activeCampaigns,
                'unassignedCPs'       => $unassignedCPs,
                'unassignedSUPs'      => $unassignedSUPs,
                'unassignedTCs'       => $unassignedTCs,
                'assignedCPs'         => $assignedCPs,
                'assignedSUPs'        => $assignedSUPs,
                'unassignedEmployees' => $unassignedEmployees,
                'campaigns'           => $campaigns,
                'myAssignment'        => null,
                'role'                => 'admin',
            ]);
        }

        // ── CP : voit ses campagnes, peut affecter SUP/TC ─────────────────────
        if ($employee?->position->code === 'CP') {
            $myCampaignIds = Assignment::where('employee_id', $employee->id)
                ->where('position_id', $cpPosition->id)
                ->where('status', 'actif')
                ->pluck('campaign_id');

            $activeCampaigns = Campaign::where('status', 'active')
                ->whereIn('id', $myCampaignIds)
                ->get();

            $assignedCPs = Assignment::where('employee_id', $employee->id)
                ->where('position_id', $cpPosition->id)
                ->where('status', 'actif')
                ->with(['employee.user', 'campaign'])
                ->get();

            $assignedSUPs = Assignment::where('manager_id', $employee->id)
                ->where('position_id', $supPosition->id)
                ->where('status', 'actif')
                ->whereIn('campaign_id', $myCampaignIds)
                ->with(['employee.user', 'campaign'])
                ->get();

            $unassignedSUPs = Employee::where('position_id', $supPosition->id)
                ->where('status', 'actif')
                ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
                ->with(['user', 'position'])->get();

            $unassignedTCs = Employee::where('position_id', $tcPosition->id)
                ->where('status', 'actif')
                ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
                ->with(['user', 'position'])->get();

            $unassignedEmployees = Employee::where('status', 'actif')
                ->whereDoesntHave('assignments', fn($q) => $q->where('status', 'actif'))
                ->whereIn('position_id', [$supPosition->id, $tcPosition->id])
                ->with([
                    'position', 'user',
                    'assignments' => fn($q) => $q->where('status', 'terminé')
                        ->latest('end_date')->limit(1)->with('campaign'),
                ])
                ->get()
                ->map(function ($emp) {
                    $emp->last_assignment = $emp->assignments->first();
                    unset($emp->assignments);
                    return $emp;
                });

            $campaigns = $this->buildCampaignTree(
                Campaign::whereIn('id', $myCampaignIds)->get(),
                $cpPosition, $supPosition, $tcPosition
            );

            return Inertia::render('Assignments/Index', [
                'activeCampaigns'     => $activeCampaigns,
                'unassignedCPs'       => [],
                'unassignedSUPs'      => $unassignedSUPs,
                'unassignedTCs'       => $unassignedTCs,
                'assignedCPs'         => $assignedCPs,
                'assignedSUPs'        => $assignedSUPs,
                'unassignedEmployees' => $unassignedEmployees,
                'campaigns'           => $campaigns,
                'myAssignment'        => null,
                'role'                => 'cp',
            ]);
        }

        // ── SUP : voit uniquement sa hiérarchie (ses TC) ──────────────────────
        if ($employee?->position->code === 'SUP') {
            $myAssignment = Assignment::where('employee_id', $employee->id)
                ->where('status', 'actif')
                ->first();

            $campaigns = [];
            if ($myAssignment) {
                $campaigns = $this->buildCampaignTree(
                    Campaign::where('id', $myAssignment->campaign_id)->get(),
                    $cpPosition, $supPosition, $tcPosition,
                    $employee->id
                );
            }

            return Inertia::render('Assignments/Index', [
                'activeCampaigns'     => [],
                'unassignedCPs'       => [],
                'unassignedSUPs'      => [],
                'unassignedTCs'       => [],
                'assignedCPs'         => [],
                'assignedSUPs'        => [],
                'unassignedEmployees' => [],
                'campaigns'           => $campaigns,
                'myAssignment'        => null,
                'role'                => 'sup',
            ]);
        }

        // ── TC : voit uniquement son affectation ──────────────────────────────
        if ($employee?->position->code === 'TC') {
            $myAssignment = Assignment::where('employee_id', $employee->id)
                ->where('status', 'actif')
                ->with([
                    'campaign',
                    'manager' => fn($q) => $q->with('user'),
                ])
                ->first();

            return Inertia::render('Assignments/Index', [
                'activeCampaigns'     => [],
                'unassignedCPs'       => [],
                'unassignedSUPs'      => [],
                'unassignedTCs'       => [],
                'assignedCPs'         => [],
                'assignedSUPs'        => [],
                'unassignedEmployees' => [],
                'campaigns'           => [],
                'myAssignment'        => $myAssignment,
                'role'                => 'tc',
            ]);
        }

        abort(403);
    }

    // ── Helper : construire l'arbre CP > SUP > TC ─────────────────────────────
    private function buildCampaignTree(
        $campaignCollection,
        $cpPosition,
        $supPosition,
        $tcPosition,
        $supEmployeeId = null
    ) {
        return $campaignCollection->map(function ($campaign) use (
            $cpPosition, $supPosition, $tcPosition, $supEmployeeId
        ) {
            $assignments = $campaign->assignments()
                ->where('status', 'actif')
                ->with(['employee.user', 'position'])
                ->get();

            $cps  = $assignments->where('position_id', $cpPosition->id)->values();
            $sups = $assignments->where('position_id', $supPosition->id)->values();
            $tcs  = $assignments->where('position_id', $tcPosition->id)->values();

            // Filtrer sur un SUP spécifique si vue SUP
            if ($supEmployeeId) {
                $sups = $sups->where('employee_id', $supEmployeeId)->values();
            }

            $tree = $cps->map(function ($cp) use ($sups, $tcs) {
                $cpSups = $sups->where('manager_id', $cp->employee_id)->values();
                $cpSups = $cpSups->map(function ($sup) use ($tcs) {
                    $supTcs = $tcs->where('manager_id', $sup->employee_id)->values();
                    return array_merge($sup->toArray(), ['children' => $supTcs->toArray()]);
                });
                return array_merge($cp->toArray(), ['children' => $cpSups->toArray()]);
            });

            // Vue SUP : garder uniquement les branches où ce SUP apparaît
            if ($supEmployeeId) {
                $tree = $tree->filter(fn($cp) => count($cp['children']) > 0)->values();
            }

            return [
                'id'     => $campaign->id,
                'name'   => $campaign->name,
                'status' => $campaign->status,
                'tree'   => $tree,
            ];
        })->values();
    }

    // ─── Affecter un CP à une campagne (Admin uniquement) ─────────────────────
    public function assignCP(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'campaign_ids'   => 'required|array|min:1',
            'campaign_ids.*' => 'exists:campaigns,id',
            'start_date'     => 'required|date',
        ]);

        $cpPosition = Position::where('code', 'CP')->firstOrFail();

        $campaigns = Campaign::whereIn('id', $data['campaign_ids'])->get();
        foreach ($campaigns as $campaign) {
            if ($campaign->status !== 'active') {
                return back()->withErrors([
                    'campaign_ids' => "La campagne « {$campaign->name} » n'est pas active."
                ]);
            }
            
            // RÈGLE : Un seul CP par campagne
            $existingCP = Assignment::where('campaign_id', $campaign->id)
                ->where('position_id', $cpPosition->id)
                ->where('status', 'actif')
                ->first();

            if ($existingCP) {
                return back()->withErrors([
                    'campaign_ids' => "La campagne « {$campaign->name} » a déjà un Chef de Plateau assigné ({$existingCP->employee->first_name} {$existingCP->employee->last_name})."
                ]);
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

    // ─── Affecter un SUP à un CP/campagne (Admin ou CP) ───────────────────────
    public function assignSUP(Request $request)
    {
        $user     = Auth::user();
        $employee = $user->employee;

        if (!$user->isAdmin() && $employee?->position->code !== 'CP') {
            abort(403);
        }

        $data = $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'cp_assignment_id' => 'required|exists:assignments,id',
            'start_date'       => 'required|date',
        ]);

        $supPosition  = Position::where('code', 'SUP')->firstOrFail();
        $cpAssignment = Assignment::findOrFail($data['cp_assignment_id']);

        // Si CP connecté : vérifier que le cp_assignment lui appartient bien
        if (!$user->isAdmin()) {
            if ($cpAssignment->employee_id !== $employee->id) {
                return back()->withErrors([
                    'cp_assignment_id' => "Vous ne pouvez affecter que sous vos propres campagnes."
                ]);
            }
        }

        $alreadyAssigned = Assignment::where('employee_id', $data['employee_id'])
            ->where('status', 'actif')
            ->exists();
        if ($alreadyAssigned) {
            return back()->withErrors([
                'employee_id' => 'Ce Superviseur est déjà affecté à une campagne.'
            ]);
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

    // ─── Affecter un ou plusieurs TC à un SUP (Admin ou CP) ───────────────────
    public function assignTC(Request $request)
    {
        $user     = Auth::user();
        $employee = $user->employee;

        if (!$user->isAdmin() && $employee?->position->code !== 'CP') {
            abort(403);
        }

        $data = $request->validate([
            'employee_ids'      => 'required|array|min:1',
            'employee_ids.*'    => 'exists:employees,id',
            'sup_assignment_id' => 'required|exists:assignments,id',
            'start_date'        => 'required|date',
        ]);

        $tcPosition    = Position::where('code', 'TC')->firstOrFail();
        $supAssignment = Assignment::findOrFail($data['sup_assignment_id']);

        // Si CP connecté : vérifier que le SUP est bien sous lui
        if (!$user->isAdmin()) {
            if ($supAssignment->manager_id !== $employee->id) {
                return back()->withErrors([
                    'sup_assignment_id' => "Ce superviseur n'est pas sous votre hiérarchie."
                ]);
            }
        }

        foreach ($data['employee_ids'] as $employeeId) {
            $alreadyAssigned = Assignment::where('employee_id', $employeeId)
                ->where('status', 'actif')
                ->exists();
            if ($alreadyAssigned) {
                $emp = Employee::find($employeeId);
                return back()->withErrors([
                    'employee_ids' => "Le TC {$emp->first_name} {$emp->last_name} est déjà affecté."
                ]);
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

    // ─── Libérer une ressource avec cascade ───────────────────────────────────
    public function release(Assignment $assignment)
    {
        $user     = Auth::user();
        $employee = $user->employee;

        // Seuls admin et CP peuvent libérer
        if (!$user->isAdmin() && $employee?->position->code !== 'CP') {
            abort(403);
        }

        // CP : vérifier qu'il est bien le manager du SUP/TC à libérer
        if (!$user->isAdmin()) {
            $isUnderMe = $assignment->manager_id === $employee->id
                || Assignment::where('employee_id', $assignment->manager_id)
                    ->where('manager_id', $employee->id)
                    ->where('status', 'actif')
                    ->exists();

            if (!$isUnderMe) {
                abort(403, "Vous ne pouvez libérer que les ressources sous votre hiérarchie.");
            }
        }

        $cpPosition  = Position::where('code', 'CP')->firstOrFail();
        $supPosition = Position::where('code', 'SUP')->firstOrFail();

        // Cascade si CP
        if ($assignment->position_id === $cpPosition->id) {
            $sups = Assignment::where('manager_id', $assignment->employee_id)
                ->where('campaign_id', $assignment->campaign_id)
                ->where('status', 'actif')
                ->get();

            foreach ($sups as $sup) {
                Assignment::where('manager_id', $sup->employee_id)
                    ->where('campaign_id', $assignment->campaign_id)
                    ->where('status', 'actif')
                    ->update(['status' => 'terminé', 'end_date' => now()]);
            }

            Assignment::where('manager_id', $assignment->employee_id)
                ->where('campaign_id', $assignment->campaign_id)
                ->where('status', 'actif')
                ->update(['status' => 'terminé', 'end_date' => now()]);
        }

        // Cascade si SUP
        if ($assignment->position_id === $supPosition->id) {
            Assignment::where('manager_id', $assignment->employee_id)
                ->where('campaign_id', $assignment->campaign_id)
                ->where('status', 'actif')
                ->update(['status' => 'terminé', 'end_date' => now()]);
        }

        $assignment->update(['status' => 'terminé', 'end_date' => now()]);

        return redirect()->back()->with('success', 'Ressource libérée avec succès.');
    }
}