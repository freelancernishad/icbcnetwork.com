<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use App\Models\CommissionTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CommissionTransactionController;

class ProductController extends Controller
{
    // Fetch all products
    public function getAllProducts()
    {
        $products = Product::all();
        return response()->json($products);
    }

    // Buy Product and distribute commissions
    public function buyProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'channel' => 'required|string',
            'send_account' => 'required|string',
            'trx_id' => 'required|string|unique:user_products,trx_id',
        ]);

        // Create a purchase entry in the user_products table
        $purchase = UserProduct::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'channel' => $request->channel,
            'send_account' => $request->send_account,
            'trx_id' => $request->trx_id,
        ]);

        // Fetch the product and its price for commission calculation
        $product = Product::find($request->product_id);
        $productPrice = $product->price; // Assuming there's a price field in the Product model

        // Set the purchased package on the user
        $this->setUserPackage(Auth::user(), $product);

        // Distribute commissions to referrers
        $this->distributeCommissions(Auth::user(), $productPrice);

        return response()->json(['message' => 'Product purchased successfully and commissions distributed']);
    }

    // Set the purchased package on the user
    private function setUserPackage(User $user, Product $product)
    {
        // Assuming the user has a 'package_id' or similar column to store the current package
        $user->package_id = $product->id; // Or store any other relevant product information
        $user->save();
    }

    // Distribute commissions to referrers
    private function distributeCommissions(User $referredUser, $purchaseAmount)
    {
        // Get the referrer (user who referred the current user)
        $referrer = $referredUser->referredBy; // Assuming 'referredBy' relationship exists on User model

        // Loop through levels (for simplicity, we'll assume up to 3 levels)
        $currentUser = $referredUser;
        $level = 1;

        while ($referrer && $level <= 3) {
            // Fetch the commission rate for this level
            $commissionRate = Commission::where('level', $level)->first()->rate ?? 0;

            // Calculate commission
            $commissionAmount = ($commissionRate / 100) * $purchaseAmount;

            // Record the transaction
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
    }
}
