<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        return Position::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:positions',
        ]);
        
        return Position::create($validated);
    }

    public function show($id)
    {
        return Position::findOrFail($id);
    }

    public function destroy($id)
    {
        Position::findOrFail($id)->delete();
        return response()->json(['message' => 'Supprimé avec succès'], 200);
    }
}
