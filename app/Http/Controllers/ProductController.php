<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\Product;
use Illuminate\Http\Request;


class ProductController extends Controller
{
    /**
     * Menampilkan semua product.
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->query('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->query('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'productCount' => Product::count(),
            'activeProductCount' => Product::where('is_active', true)->count(),
            'lowStockCount' => Product::where('is_active', true)->where('stock', '<=', 5)->count(),
        ]);
    }

    /**
     * Menampilkan form tambah product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Menyimpan product baru.
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $file = $request->file('image');
        if ($file) {
            $original = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $original) . '.' . $ext;
            $path = 'products/' . $filename;

            $manager = new ImageManager(Driver::class);
            $image = $manager->read($file->getRealPath())->cover(800, 800);

            if ($ext === 'png') {
                $encoded = $image->encodeByExtension('png', quality: 90);
            } else {
                $encoded = $image->encodeByExtension('jpg', quality: 85);
            }

            Storage::disk('public')->put($path, $encoded->toString());
            $data['image'] = $path;
        }

        $product = Product::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'product' => $product->only(['id', 'name', 'category', 'price', 'stock', 'is_active']),
            ], 201);
        }

        return redirect()->route('products.index')
            ->with('success', 'Produk ditambahkan');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $validated['image'] = null;
        }

        $product->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'product' => $product->only(['id', 'name', 'category', 'price', 'stock', 'is_active']),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('products.index')->with('success', 'Product berhasil dihapus.');
    }
}