<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // ===== GET ALL PRODUCTS =====
    public function index(Request $request)
    {
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
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status (active only)
        // $query->where('status', 'active');

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
                case 'rating':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'popular':
                    $query->orderBy('sold_count', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // ===== GET SINGLE PRODUCT =====
    public function show($id)
    {
        $product = Product::with(['vendor', 'category', 'images', 'reviews'])
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
    }

    // ===== GET FEATURED PRODUCTS =====
    public function featured()
    {
        $products = Product::with(['vendor', 'category', 'images'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->where('stock_quantity', '>', 0)
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // ===== GET PRODUCTS BY CATEGORY =====
    public function byCategory($categoryId)
    {
        $category = Category::find($categoryId);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $products = Product::with(['vendor', 'category', 'images'])
            ->where('status', 'active')
            ->where('category_id', $categoryId)
            ->where('stock_quantity', '>', 0)
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // ===== SEARCH PRODUCTS =====
    public function search(Request $request)
    {
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
            ->where('status', 'active')
            ->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->q}%")
                  ->orWhere('description', 'LIKE', "%{$request->q}%");
            })
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // ===== CREATE PRODUCT (Vendor Only) =====
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|unique:products',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor || !$vendor->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'You must be an approved vendor to add products'
            ], 403);
        }

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'compare_price' => $request->compare_price,
            'stock_quantity' => $request->stock_quantity,
            'sku' => $request->sku,
            'is_featured' => $request->is_featured ?? false,
            'is_new' => $request->is_new ?? true,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    // ===== UPDATE PRODUCT (Vendor Only) =====
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor || $product->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this product'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'status' => 'sometimes|in:draft,pending,active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    // ===== DELETE PRODUCT (Vendor Only) =====
    public function destroy(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor || $product->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this product'
            ], 403);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}