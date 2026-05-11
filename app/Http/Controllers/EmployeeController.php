<?php

namespace App\Http\Controllers;

use App\Models\Employee;
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

    public function destroy($id){
        try {
            $employee = Employee::findOrFail($id);
            $employee->update(['status' => 'suspendu']);
            return response()->json(['message'=> 'Employé désactivé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['error'=> 'Erreur lors de la désactivation'], 500);
        }
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
