<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    // Create or Update Commission for a specific level
    public function setCommissionRate(Request $request)
    {
        $request->validate([
            'level' => 'required|integer|min:1',
            'rate' => 'required|numeric|min:0',
        ]);

        // Find or create a commission for the given level
        $commission = Commission::updateOrCreate(
            ['level' => $request->level],
            ['rate' => $request->rate]
        );

        return response()->json(['message' => 'Commission rate set successfully', 'commission' => $commission]);
    }

    // List all Commission Rates
    public function getCommissionRates()
    {
        $commissions = Commission::all();
        return response()->json($commissions);
    }
}
