<?php

namespace App\Http\Controllers\Api;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    // ===== GET USER'S WISHLIST =====
    public function index(Request $request)
    {
        $user = $request->user();
        
        $wishlist = Wishlist::with(['product', 'product.images', 'product.vendor'])
            ->where('user_id', $user->id)
            ->get();

        // Format response
        $items = $wishlist->map(function ($item) {
            $product = $item->product;
            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'compare_price' => $product->compare_price,
                'discount_percentage' => $product->discount_percentage,
                'image' => $product->images->first()->image_url ?? null,
                'vendor' => $product->vendor->shop_name ?? null,
                'rating' => $product->rating,
                'reviews_count' => $product->reviews_count,
                'is_on_sale' => $product->is_on_sale,
                'stock_status' => $product->stock_status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => $items->count(),
            ]
        ]);
    }

    // ===== ADD TO WISHLIST =====
    public function add(Request $request, $productId)
    {
        $user = $request->user();

        // Check if product exists
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Check if product is already in wishlist
        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist'
            ], 400);
        }

        // Add to wishlist
        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'data' => [
                'id' => $wishlist->id,
                'product_id' => $productId,
                'wishlist_count' => Wishlist::where('user_id', $user->id)->count(),
            ]
        ], 201);
    }

    // ===== REMOVE FROM WISHLIST =====
    public function remove(Request $request, $productId)
    {
        $user = $request->user();

        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in wishlist'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist',
            'data' => [
                'wishlist_count' => Wishlist::where('user_id', $user->id)->count(),
            ]
        ]);
    }

    // ===== CHECK IF PRODUCT IS IN WISHLIST =====
    public function check(Request $request, $productId)
    {
        $user = $request->user();

        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $exists,
            ]
        ]);
    }
}