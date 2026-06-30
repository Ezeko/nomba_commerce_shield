<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $store = Auth::user()->store;
        $products = $store->products()->latest()->paginate(10);

        return view('merchant.products.index', compact('store', 'products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $store = Auth::user()->store;
        return view('merchant.products.create', compact('store'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $store = Auth::user()->store;

        // Validation is handled by StoreProductRequest


        Product::create([
            'store_id' => $store->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(100, 999),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_url' => $request->image_url ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500', // default premium placeholder
            'is_active' => true,
        ]);

        return redirect()->route('merchant.products.index')
            ->with('success', 'Product added successfully.');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $store = Auth::user()->store;
        if ($product->store_id !== $store->id) {
            abort(403);
        }

        return view('merchant.products.edit', compact('store', 'product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $store = Auth::user()->store;
        if ($product->store_id !== $store->id) {
            abort(403);
        }

        // Validation is handled by UpdateProductRequest


        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_url' => $request->image_url,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('merchant.products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $store = Auth::user()->store;
        if ($product->store_id !== $store->id) {
            abort(403);
        }

        $product->delete();

        return redirect()->route('merchant.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
