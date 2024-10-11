<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;
use App\Models\CommissionTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // Fetch all products
    public function getAllProducts()
    {
        $products = Product::all();
        return response()->json($products);
    }

    // Buy Product and create a purchase entry with pending status
    public function buyProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'channel' => 'required|string',
            'send_account' => 'required|string',
            'trx_id' => 'required|string|unique:user_products,trx_id',
        ]);

        // Create a purchase entry in the user_products table with pending status
        $purchase = UserProduct::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'channel' => $request->channel,
            'send_account' => $request->send_account,
            'trx_id' => $request->trx_id,
            'status' => 'pending', // Set status to pending
        ]);

        return response()->json(['message' => 'Product purchase initiated and is pending approval']);
    }

    // Approve Product Payment
    public function approvePayment($id)
    {
        $purchase = UserProduct::findOrFail($id);
        if ($purchase->status === 'pending') {
            $purchase->status = 'approved';
            $purchase->save();

            // Fetch the product and its price for commission calculation
            $product = Product::find($purchase->product_id);
            $productPrice = $product->price; // Assuming there's a price field in the Product model

            // Set the purchased package on the user
            $this->setUserPackage(Auth::user(), $product);

            // Distribute commissions to referrers
            $this->distributeCommissions(Auth::user(), $productPrice);

            return response()->json(['message' => 'Payment approved and commissions distributed']);
        }

        return response()->json(['message' => 'Payment is already approved or cannot be approved'], 400);
    }

    // Reject Product Payment
    public function rejectPayment($id)
    {
        $purchase = UserProduct::findOrFail($id);
        if ($purchase->status === 'pending') {
            $purchase->status = 'rejected';
            $purchase->save();

            return response()->json(['message' => 'Payment rejected successfully']);
        }

        return response()->json(['message' => 'Payment is already processed or cannot be rejected'], 400);
    }

    // List all pending payments
    public function listPendingPayments()
    {
        $pendingPayments = UserProduct::where('status', 'pending')->get();
        return response()->json($pendingPayments);
    }

    // List all approved payments
    public function listApprovedPayments()
    {
        $approvedPayments = UserProduct::where('status', 'approved')->get();
        return response()->json($approvedPayments);
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

    // Create a new product
    public function createProduct(Request $request)
    {
        // Validate request data
        $request->validate([
            'daily_percentage' => 'required|numeric|min:0',
            'total_percentage' => 'required|numeric|min:0',
            'daily_income' => 'required|numeric|min:0',
            'total_earnings' => 'required|numeric|min:0',
            'days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'cashback' => 'required|numeric|min:0',
        ]);

        // Create the product
        $product = Product::create([
            'daily_percentage' => $request->daily_percentage,
            'total_percentage' => $request->total_percentage,
            'daily_income' => $request->daily_income,
            'total_earnings' => $request->total_earnings,
            'days' => $request->days,
            'price' => $request->price,
            'cashback' => $request->cashback,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ]);
    }
}
