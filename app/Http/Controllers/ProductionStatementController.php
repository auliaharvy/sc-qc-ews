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

        // Determine if it's day or night shift based on current time
        $currentTime = now()->setTimezone('Asia/Jakarta')->format('H:i');
        $shift = '';

        if ($currentTime >= '09:00' && $currentTime < '21:00') {
            $shift = 'day';
        } else {
            $shift = 'night';
        }
        // Add shift to validated data
        $validated['shift'] = $shift;

        ProductionStatement::create($validated);

        return redirect()->route('home')
            ->with('success', 'Production statement has been recorded.');
    }
}
