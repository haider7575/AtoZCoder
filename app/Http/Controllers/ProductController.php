<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index()
    {
        // Admin sees all, Staff/Users see active? 
        // For simplicity, just paginate all.
        return Product::latest()->paginate(10);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-products');

        $validated = $request->validate([
            'name' => 'required|string',
            'sku' => 'required|string|unique:products',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $product = Product::create($validated);

        if ($request->wantsJson()) {
            return response()->json($product, 201);
        }
        return redirect()->back()->with('success', 'Product Created');
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        Gate::authorize('manage-products');

        $validated = $request->validate([
            'name' => 'string',
            'sku' => 'string|unique:products,sku,' . $product->id,
            'price' => 'numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'status' => 'in:active,inactive',
        ]);

        $product->update($validated);

        if ($request->wantsJson()) {
            return response()->json($product);
        }
        return redirect()->back()->with('success', 'Product Updated');
    }

    public function destroy(Request $request, Product $product)
    {
        Gate::authorize('manage-products');

        $product->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Product deleted']);
        }
        return redirect()->back()->with('success', 'Product Deleted');
    }
}
