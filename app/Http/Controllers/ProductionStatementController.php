<?php

namespace App\Http\Controllers;

use App\Models\ProductionStatement;
use Illuminate\Http\Request;

class ProductionStatementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'status' => 'required|in:production,no_production',
            'reason' => 'required_if:status,no_production',
            'date' => 'required',
        ]);

        ProductionStatement::create($validated);

        return redirect()->route('home')
            ->with('success', 'Production statement has been recorded.');
    }
}
