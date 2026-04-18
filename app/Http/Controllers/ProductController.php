<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\AdminLogger;
use Illuminate\Support\Facades\Mail;
use App\Models\NewsletterSubscriber;
use App\Mail\NewProductNotification;

class ProductController extends Controller
{
    /**
     * Display a listing of products for the admin.
     */
    public function index()
    {
        $products = Product::latest()->get();
        $orders = \App\Models\Order::with('items.product')->latest()->get();
        $heroBanners = \App\Models\HeroBanner::orderBy('sort_order')->get();
        $subcategoryImages = \App\Models\SubcategoryImage::all()->keyBy('subcategory');
        return view('dashboard', compact('products', 'orders', 'heroBanners', 'subcategoryImages'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'price' => 'required|numeric',
            'category' => 'required|string|max:50',
            'subcategory' => 'required|in:Formals,Ready to Wear,Luxury Lawn,Unstitched',
            'stock_quantity' => 'required|integer|min:0',
            'sizes' => 'nullable|string|max:255',
            'types' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_price' => 'nullable|numeric|min:0',
            'stitched_price' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:50',
            'variant_group_id' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'secondary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $filename);
            $validated['image_path'] = 'images/products/' . $filename;
        }

        if ($request->hasFile('secondary_image')) {
            $file     = $request->file('secondary_image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $filename);
            $validated['secondary_image_path'] = 'images/products/' . $filename;
        }

        $validated['is_featured'] = $request->has('is_featured');
        $product = Product::create($validated);

        // Log admin action
        AdminLogger::log('create_product', 'Product', $product->id, ['name' => $product->name]);

        // Send newsletter notification to all subscribers
        try {
            $subscribers = NewsletterSubscriber::pluck('email');
            foreach ($subscribers as $email) {
                Mail::to($email)->send(new NewProductNotification($product));
            }
        } catch (\Exception $e) {
            // Silent fail — don't block the admin if email fails
        }

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/products'), $filename);
                $relPath  = 'images/products/' . $filename;
                $product->images()->create(['image_path' => $relPath]);

                // Always set first gallery image as primary if no primary set
                if ($index === 0 && !$product->image_path) {
                    $product->update(['image_path' => $relPath]);
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Product added successfully!');
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:50',
            'subcategory' => 'required|in:Formals,Ready to Wear,Luxury Lawn,Unstitched',
            'stock_quantity' => 'required|integer|min:0',
            'sizes' => 'nullable|string|max:255',
            'types' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_price' => 'nullable|numeric|min:0',
            'stitched_price' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:50',
            'variant_group_id' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'secondary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('secondary_image')) {
            // Delete old secondary image if exists
            if ($product->secondary_image_path && file_exists(public_path($product->secondary_image_path))) {
                @unlink(public_path($product->secondary_image_path));
            }
            $file     = $request->file('secondary_image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/products'), $filename);
            $validated['secondary_image_path'] = 'images/products/' . $filename;
        }

        $validated['is_featured'] = $request->has('is_featured');
        $product->update($validated);

        // Log admin action
        AdminLogger::log('update_product', 'Product', $product->id, ['name' => $product->name]);

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/products'), $filename);
                $relPath  = 'images/products/' . $filename;
                $product->images()->create(['image_path' => $relPath]);

                if ($index === 0 && !$product->image_path) {
                    $product->update(['image_path' => $relPath]);
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->image_path && file_exists(public_path($product->image_path))) {
            @unlink(public_path($product->image_path));
        }
        // Log admin action before deleting
        AdminLogger::log('delete_product', 'Product', $product->id, ['name' => $product->name]);
        $product->delete();

        return redirect()->route('dashboard')->with('success', 'Product deleted successfully!');
    }

    /**
     * Remove multiple products from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'No products selected.'], 400);
        }

        $products = Product::whereIn('id', $ids)->get();

        // Log admin action
        AdminLogger::log('bulk_delete_products', 'Product', null, [
            'count' => $products->count(),
            'product_ids' => $products->pluck('id')->toArray(),
        ]);

        foreach ($products as $product) {
            if ($product->image_path && file_exists(public_path($product->image_path))) {
                @unlink(public_path($product->image_path));
            }
            $product->delete();
        }

        return response()->json(['success' => true, 'message' => 'Selected products deleted successfully!']);
    }

    /**
     * Public store listing.
     */
    public function storeFront(Request $request, $category = null, $subcategory = null)
    {
        $query = Product::query();

        if ($category) {
            $query->where('category', strtoupper($category) === 'WOMEN' ? 'WOMEN' : ucfirst($category));
        }

        $sub = $subcategory ?: $request->subcategory;
        if ($sub) {
            $query->where('subcategory', $sub);
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->has('size')) {
            $query->where('sizes', 'like', '%' . $request->size . '%');
        }

        // If it's the root home page with no filters and NOT explicitly "View All"
        if (!$category && !$subcategory && !$request->has('search') && !$request->has('price_min') && !$request->has('price_max') && !$request->has('size') && !$request->has('subcategory') && !$request->has('view_all')) {
            $sections = [
                [
                    'title' => 'FORMALS',
                    'category' => null,
                    'subcategory' => 'Formals',
                    'products' => Product::where('subcategory', 'Formals')->where('is_featured', true)->latest()->get()
                ],
                [
                    'title' => 'READY TO WEAR',
                    'category' => null,
                    'subcategory' => 'Ready to Wear',
                    'products' => Product::where('subcategory', 'Ready to Wear')->where('is_featured', true)->latest()->get()
                ],
                [
                    'title' => 'LUXURY LAWN',
                    'category' => null,
                    'subcategory' => 'Luxury Lawn',
                    'products' => Product::where('subcategory', 'Luxury Lawn')->where('is_featured', true)->latest()->get()
                ],
                [
                    'title' => 'UNSTITCHED',
                    'category' => null,
                    'subcategory' => 'Unstitched',
                    'products' => Product::where('subcategory', 'Unstitched')->where('is_featured', true)->latest()->get()
                ],
            ];

            return view('welcome', compact('sections', 'category', 'subcategory'));
        }

        $products = $query->latest()->get();
        
        return view('welcome', compact('products', 'category', 'subcategory'));
    }

    /**
     * Show detailed product page
     */
    public function show(Product $product)
    {
        $product->load(['images', 'reviews']);
        
        $colorVariants = $product->colorVariants();

        // Also get related products (same category, excluding current)
        $relatedProducts = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('product-details', compact('product', 'relatedProducts', 'colorVariants'));
    }

    /**
     * Submit a customer review
     */
    public function submitReview(Request $request, Product $product)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $product->reviews()->create([
            'customer_name' => $validated['customer_name'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'is_approved' => true, // Auto-approve for demo
        ]);

        return back()->with('success', 'Thank you for your review!');
    }

    /**
     * Toggle the featured status of a product.
     */
    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        return response()->json(['success' => true, 'is_featured' => $product->is_featured]);
    }
    /**
     * Toggle the top5 status of a product.
     */
    public function toggleTop5(Product $product)
    {
        $product->update(['is_top5' => !$product->is_top5]);
        return response()->json(['success' => true, 'is_top5' => $product->is_top5]);
    }

    /**
     * Get details for multiple products (for Wishlist).
     */
    public function getWishlistDetails(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return response()->json([]);
        }

        $products = Product::whereIn('id', $ids)->get()->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => number_format($product->price),
                'discount_price' => $product->discount_price ? number_format($product->discount_price) : null,
                'image' => asset($product->image_path ?: 'placeholder.png'),
                'url' => route('product.show', $product)
            ];
        });

        return response()->json($products);
    }
}
