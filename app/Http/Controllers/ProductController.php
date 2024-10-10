<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\UserProduct;
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

    // Buy Product
    public function buyProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'channel' => 'required|string',
            'send_account' => 'required|string',
            'trx_id' => 'required|string|unique:user_products,trx_id',
        ]);

        $purchase = UserProduct::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'channel' => $request->channel,
            'send_account' => $request->send_account,
            'trx_id' => $request->trx_id,
        ]);

        return response()->json(['message' => 'Product purchased successfully']);
    }
}
