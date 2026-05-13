<?php
 
namespace App\Http\Controllers;
 
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Http\Request;
 
class EmployeeController extends Controller
{
    public function index() {
        return Employee::with('position')->orderBy('created_at', 'desc')->paginate(50);
    }
 
    public function store(Request $request){
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:employees',
            'address' => 'nullable|string',
            'position_id' => 'required|exists:positions,id',
            'salary_base' => 'required|numeric|min:0',
            'status' => 'required|in:actif,suspendu,inactif',
        ]);
 
        // ***********************************************************************************************Generration  automatiquement le matricule
        $validated['matricule'] = $this->generateMatricule();
       
        $validated['user_id'] = auth()->id() ?? 1;  
 
        $employee = Employee::create($validated);
        return $employee->load('position');
    }
 
    public function show($id){
        $employee = Employee::with(['position', 'assignments.campaign', 'assignments.position', 'assignments.manager'])->findOrFail($id);
       
        return \Inertia\Inertia::render('Employees/Show', [
            'employee' => $employee
        ]);
    }
 
    public function update(Request $request, $id){
        try {
            $employee = Employee::findOrFail($id);
 
            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'birth_date' => 'sometimes|date',
                'phone' => 'sometimes|string|max:20',
                'email' => 'sometimes|email|unique:employees,email,'.$id,
                'address' => 'nullable|string',
                'position_id' => 'sometimes|exists:positions,id',
                'salary_base' => 'sometimes|numeric|min:0',
                'status' => 'sometimes|in:actif,suspendu,inactif',
            ]);
 
            $employee->update($validated);
            return response()->json(['message'=> 'Mis à jour avec succès', 'employee' => $employee->load('position')], 200);
        } catch (\Exception $e) {
            return response()->json(['error'=> 'Erreur lors de la mise à jour'], 500);
        }
    }
 
    public function destroy(Request $request, $id){
        try {
            $employee = Employee::findOrFail($id);
            $replacementId = $request->input('replacement_id');

            // 1. Gérer la hiérarchie dans les affectations
            $activeAssignments = $employee->activeAssignments;
            
            foreach ($activeAssignments as $assignment) {
                if ($replacementId) {
                    // Transférer les subordonnés au remplaçant
                    Assignment::where('manager_id', $employee->id)
                        ->where('campaign_id', $assignment->campaign_id)
                        ->where('status', 'actif')
                        ->update(['manager_id' => $replacementId]);
                    
                    // Créer une nouvelle affectation pour le remplaçant si nécessaire
                    // (Optionnel : on peut considérer que le remplaçant doit déjà être libre ou on l'affecte ici)
                    $alreadyAssigned = Assignment::where('employee_id', $replacementId)
                        ->where('campaign_id', $assignment->campaign_id)
                        ->where('status', 'actif')
                        ->exists();
                    
                    if (!$alreadyAssigned) {
                        Assignment::create([
                            'employee_id' => $replacementId,
                            'campaign_id' => $assignment->campaign_id,
                            'position_id' => $assignment->position_id,
                            'manager_id'  => $assignment->manager_id,
                            'status'      => 'actif',
                            'start_date'  => now(),
                        ]);
                    }
                } else {
                    // Pas de remplaçant : les subordonnés n'ont plus de manager direct pour cette campagne
                    Assignment::where('manager_id', $employee->id)
                        ->where('campaign_id', $assignment->campaign_id)
                        ->where('status', 'actif')
                        ->update(['manager_id' => null]);
                }

                // Terminer l'affectation actuelle
                $assignment->update([
                    'status' => 'terminé',
                    'end_date' => now()
                ]);
            }

            // 2. Désactiver le compte utilisateur s'il existe
            if ($employee->user) {
                $employee->user->update(['is_active' => false]);
            }

            // 3. Mettre à jour le statut de l'employé
            $employee->update(['status' => 'suspendu']);

            return response()->json(['message'=> 'Employé désactivé et hiérarchie mise à jour'], 200);
        } catch (\Exception $e) {
            return response()->json(['error'=> 'Erreur lors de la désactivation: ' . $e->getMessage()], 500);
        }
    }

    public function getAvailableReplacements($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Employés ayant la même position, étant actifs et n'étant pas l'employé lui-même
        $replacements = Employee::where('position_id', $employee->position_id)
            ->where('status', 'actif')
            ->where('id', '!=', $id)
            ->get();
            
        return response()->json($replacements);
    }
 
    private function generateMatricule()
    {
        $year = date('Y');
        $lastEmployee = Employee::where('matricule', 'like', $year.'%')
            ->orderBy('matricule', 'desc')
            ->first();
 
        if ($lastEmployee) {
            $lastNumber = (int) substr($lastEmployee->matricule, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
 
        return $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
 
 
 
 