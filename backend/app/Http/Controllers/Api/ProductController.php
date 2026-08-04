<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of products (Public)
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['vendor', 'category', 'images']);

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by vendor
            if ($request->has('vendor_id')) {
                $query->where('vendor_id', $request->vendor_id);
            }

            // Filter by price range
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status (active only for public)
            $query->where('is_active', true);

            // Filter by stock
            if ($request->has('in_stock')) {
                $query->where('stock_quantity', '>', 0);
            }

            // Sort
            if ($request->has('sort')) {
                switch ($request->sort) {
                    case 'price-low':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price-high':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $products = $query->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Product index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products'
            ], 500);
        }
    }

    /**
     * Get vendor's own products
     */
    public function vendorProducts(Request $request)
    {
        try {
            $vendorId = auth()->id();
            
            $query = Product::with(['category', 'images'])
                ->where('vendor_id', $vendorId);

            // Search
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Sort
            $query->orderBy('created_at', 'desc');

            $products = $query->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor products error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vendor products'
            ], 500);
        }
    }

    /**
     * Display the specified product (Public)
     */
    public function show($id)
    {
        try {
            $product = Product::with(['vendor', 'category', 'images', 'reviews.user'])
                ->where('is_active', true)
                ->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // Increment view count
            $product->increment('view_count');

            return response()->json([
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Product show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product'
            ], 500);
        }
    }

    /**
     * Get featured products (Public)
     */
    public function featured()
    {
        try {
            $products = Product::with(['vendor', 'category', 'images'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->where('stock_quantity', '>', 0)
                ->limit(8)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Featured products error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured products'
            ], 500);
        }
    }

    /**
     * Get products by category (Public)
     */
    public function byCategory($categoryId)
    {
        try {
            $category = Category::find($categoryId);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $products = Product::with(['vendor', 'category', 'images'])
                ->where('is_active', true)
                ->where('category_id', $categoryId)
                ->where('stock_quantity', '>', 0)
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Category products error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category products'
            ], 500);
        }
    }

    /**
     * Search products (Public)
     */
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:2',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $products = Product::with(['vendor', 'category', 'images'])
                ->where('is_active', true)
                ->where(function($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->q}%")
                      ->orWhere('description', 'LIKE', "%{$request->q}%")
                      ->orWhere('sku', 'LIKE', "%{$request->q}%");
                })
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Search products error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search products'
            ], 500);
        }
    }

    /**
     * Store a newly created product (Vendor Only)
     */
    public function store(Request $request)
    {
        try {
            Log::info('Product creation started', ['user_id' => $request->user()?->id]);

            // ✅ Enhanced validation with custom messages
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string|min:10',
                'price' => 'required|numeric|min:0.01',
                'compare_price' => 'nullable|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'sku' => 'nullable|string|max:50|unique:products,sku',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ], [
                'name.required' => 'Product name is required',
                'description.required' => 'Description is required',
                'description.min' => 'Description must be at least 10 characters',
                'price.required' => 'Price is required',
                'price.min' => 'Price must be greater than 0',
                'category_id.required' => 'Please select a category',
                'category_id.exists' => 'Selected category does not exist',
                'stock_quantity.required' => 'Stock quantity is required',
                'stock_quantity.min' => 'Stock quantity cannot be negative',
                'sku.unique' => 'This SKU is already used by another product',
                'images.*.image' => 'Uploaded file must be an image',
                'images.*.mimes' => 'Image must be JPEG, PNG, JPG, GIF, or WEBP',
                'images.*.max' => 'Image size must be less than 2MB',
            ]);

            if ($validator->fails()) {
                Log::warning('Product validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            
            // ✅ Check if user is vendor or admin
            if (!in_array($user->role, ['vendor', 'admin'])) {
                Log::warning('Non-vendor tried to create product', ['user_id' => $user->id, 'role' => $user->role]);
                return response()->json([
                    'success' => false,
                    'message' => 'You must be a vendor to add products'
                ], 403);
            }

            // ✅ Check if vendor is approved (if not admin)
            if ($user->role === 'vendor') {
                // Check if vendor record exists and is approved
                if (!$user->vendor) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vendor profile not found. Please complete your vendor registration.'
                    ], 403);
                }
                
                if (!$user->vendor->is_approved) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your vendor account is not approved yet. Please wait for admin approval.'
                    ], 403);
                }
            }

            // ✅ Generate SKU if not provided
            $sku = $request->sku;
            if (empty($sku)) {
                $sku = strtoupper(Str::random(6)) . '-' . rand(100, 999);
                while (Product::where('sku', $sku)->exists()) {
                    $sku = strtoupper(Str::random(6)) . '-' . rand(100, 999);
                }
            }

            // ✅ Generate unique slug
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            // ✅ Create product
            $product = Product::create([
                'vendor_id' => $user->id,
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'price' => $request->price,
                'compare_price' => $request->compare_price ?? null,
                'stock_quantity' => $request->stock_quantity,
                'sku' => $sku,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
                'is_featured' => $request->has('is_featured') ? (bool)$request->is_featured : false,
                'status' => 'active',
            ]);

            Log::info('Product created', ['product_id' => $product->id]);

            // ✅ Handle images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => !ProductImage::where('product_id', $product->id)->exists(),
                    ]);
                }
                Log::info('Images uploaded', ['product_id' => $product->id, 'count' => count($request->file('images'))]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'images'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Product creation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save product. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified product (Vendor Only)
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $user = $request->user();

            // Check ownership
            if ($product->vendor_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to update this product'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|min:10',
                'price' => 'sometimes|numeric|min:0.01',
                'compare_price' => 'nullable|numeric|min:0',
                'stock_quantity' => 'sometimes|integer|min:0',
                'category_id' => 'sometimes|exists:categories,id',
                'sku' => 'sometimes|string|max:50|unique:products,sku,' . $product->id,
                'is_active' => 'sometimes|boolean',
                'is_featured' => 'sometimes|boolean',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ], [
                'description.min' => 'Description must be at least 10 characters',
                'price.min' => 'Price must be greater than 0',
                'sku.unique' => 'This SKU is already used by another product',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update slug if name changes
            if ($request->has('name') && $request->name !== $product->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $counter = 1;
                while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter++;
                }
                $product->slug = $slug;
            }

            // Update product
            $product->fill($request->all());
            $product->save();

            // Handle new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_primary' => false,
                    ]);
                }
            }

            Log::info('Product updated', ['product_id' => $product->id]);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load(['category', 'images'])
            ]);

        } catch (\Exception $e) {
            Log::error('Product update error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified product (Vendor Only)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $user = $request->user();

            // Check ownership
            if ($product->vendor_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this product'
                ], 403);
            }

            // Delete images from storage
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }

            $product->delete();

            Log::info('Product deleted', ['product_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Product deletion error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Delete product image
     */
    public function deleteImage(Request $request, $imageId)
    {
        try {
            $image = ProductImage::find($imageId);
            
            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found'
                ], 404);
            }

            $product = $image->product;
            $user = $request->user();

            // Check ownership
            if ($product->vendor_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Delete from storage
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            $image->delete();

            // Set another image as primary if needed
            if ($image->is_primary) {
                $newPrimary = ProductImage::where('product_id', $product->id)->first();
                if ($newPrimary) {
                    $newPrimary->update(['is_primary' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Image deletion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get low stock products for vendor
     */
    public function lowStock(Request $request)
    {
        try {
            $vendorId = auth()->id();
            $threshold = $request->threshold ?? 5;

            $products = Product::where('vendor_id', $vendorId)
                ->where('stock_quantity', '<=', $threshold)
                ->where('is_active', true)
                ->orderBy('stock_quantity', 'asc')
                ->with(['category', 'images'])
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Low stock products error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch low stock products'
            ], 500);
        }
    }

    /**
     * Update product stock
     */
    public function updateStock(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $user = $request->user();

            // Check ownership
            if ($product->vendor_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'stock_quantity' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $product->update(['stock_quantity' => $request->stock_quantity]);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Stock update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock'
            ], 500);
        }
    }

    /**
     * Toggle product status (active/inactive)
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $user = $request->user();

            // Check ownership
            if ($product->vendor_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $product->is_active = !$product->is_active;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Toggle status error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product status'
            ], 500);
        }
    }

    /**
     * Duplicate product
     */
    public function duplicate(Request $request, $id)
    {
        try {
            $originalProduct = Product::with(['images'])->find($id);

            if (!$originalProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 400);
            }

            $user = $request->user();

            // Check ownership
            if ($originalProduct->vendor_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Create duplicate
            $newProduct = $originalProduct->replicate();
            $newProduct->name = $originalProduct->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(4);
            $newProduct->sku = $originalProduct->sku . '-COPY';
            $newProduct->status = 'draft';
            $newProduct->is_active = false;
            $newProduct->save();

            // Duplicate images
            foreach ($originalProduct->images as $image) {
                $newPath = 'products/' . Str::random(40) . '.' . pathinfo($image->image_path, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($image->image_path, $newPath);
                
                ProductImage::create([
                    'product_id' => $newProduct->id,
                    'image_path' => $newPath,
                    'is_primary' => $image->is_primary,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product duplicated successfully',
                'data' => $newProduct->load(['category', 'images'])
            ]);

        } catch (\Exception $e) {
            Log::error('Product duplication error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate product'
            ], 500);
        }
    }
}