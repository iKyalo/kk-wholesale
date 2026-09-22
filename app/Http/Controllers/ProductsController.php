<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::all();

        $totalProducts = Product::count();

        $activeProducts = Product::where('is_active', true)->count();

        $inactiveProducts = Product::where('is_active', false)->count();

        $lowStockProducts = Product::whereHas('inventories', function ($query) {
            $query->whereColumn(
                'quantity',
                '<=',
                'products.minimum_stock'
            );
        })->count();

        return view('products.index', compact(
            'products',
            'activeProducts',
            'inactiveProducts',
            'totalProducts',
            'lowStockProducts'
        ));
    }

    public function create()
    {
        $branches = Branch::all();

        return view('products.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'sku'           => ['required', 'string', 'max:100', 'unique:products,sku'],
            'barcode'       => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'category_id'   => ['nullable', 'exists:categories,id'],
            'description'   => ['nullable', 'string'],
            'cost_price'    => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active'     => ['required', 'boolean'],
            'image'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('image')) {
                $validated['image_url'] = $request->file('image')
                    ->store('products', 'public');
            }

            unset($validated['image']);

            Product::create($validated);
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load([
            'inventories.store',
        ]);
    
        $storeInventory = $product->inventories->groupBy('store_id');
    
        return view('products.show', compact(
            'product',
            'storeInventory'
        ));
    }

    public function edit(Product $product)
    {
        $branches = Branch::all();

        return view('products.edit', compact('product', 'branches'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'sku'           => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'barcode'       => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($product->id),
            ],
            'category_id'   => ['nullable', 'exists:categories,id'],
            'description'   => ['nullable', 'string'],
            'cost_price'    => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active'     => ['required', 'boolean'],
            'image'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $validated, $product) {

            if ($request->hasFile('image')) {

                // Delete old image
                if ($product->image_url) {
                    Storage::disk('public')->delete($product->image_url);
                }

                // Store new image
                $validated['image_url'] = $request->file('image')
                    ->store('products', 'public');
            }

            unset($validated['image']);

            $product->update($validated);
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {

            if ($product->image_url) {
                Storage::disk('public')->delete($product->image_url);
            }

            $product->delete();
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}