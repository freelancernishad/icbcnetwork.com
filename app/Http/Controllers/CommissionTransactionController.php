<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Commission;
use App\Models\CommissionTransaction;
use Illuminate\Http\Request;

class CommissionTransactionController extends Controller
{
    // Function to distribute commission when a referred user makes a purchase
    public function distributeCommission(User $referredUser, $purchaseAmount)
    {
        // Get the referrer (user who referred the current user)
        $referrer = $referredUser->referredBy;

        // Loop through levels
        $currentUser = $referredUser;
        $level = 1;

        while ($referrer && $level <= 3) {
            // Fetch the commission rate for this level from the Commission table
            $commission = Commission::where('level', $level)->first();

            if (!$commission) {
                break; // If no commission rate is defined for the current level, stop the loop
            }

            // Calculate the commission based on the rate for this level
            $commissionAmount = ($commission->rate / 100) * $purchaseAmount;

            // Record the transaction in the CommissionTransaction table
            CommissionTransaction::create([
                'user_id' => $referrer->id,
                'referred_user_id' => $currentUser->id,
                'commission_amount' => $commissionAmount,
                'purchase_amount' => $purchaseAmount,
            ]);

            // Prepare for the next level
            $currentUser = $referrer;
            $referrer = $currentUser->referredBy; // Move to the next referrer in the chain
            $level++;
        }

        return response()->json(['message' => 'Commission distributed successfully']);
    }
}
