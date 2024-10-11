<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\UserProduct;
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

        // Fetch the product price for commission calculation
        $product = Product::find($request->product_id);
        $productPrice = $product->price; // Assuming there's a price field in the Product model

        // Distribute commissions to referrers
        $this->distributeCommissions($purchase->user_id, $productPrice);

        return response()->json(['message' => 'Product purchased successfully and commissions distributed']);
    }

    // Distribute commissions to referrers
    private function distributeCommissions($userId, $purchaseAmount)
    {
        $user = User::find($userId);

        // Call the CommissionTransactionController to handle the commission distribution
        app(CommissionTransactionController::class)->distributeCommission($user, $purchaseAmount);
    }
}
