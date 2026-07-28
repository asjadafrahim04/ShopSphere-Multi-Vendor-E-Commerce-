<?php

namespace App\Http\Controllers\Api;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $wishlist = Wishlist::with(['product', 'product.images', 'product.vendor'])
                ->where('user_id', $user->id)
                ->get();

            $items = $wishlist->map(function ($item) {
                $product = $item->product;
                return [
                    'id' => $item->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->images->first()->image_url ?? null,
                    'vendor' => $product->vendor->shop_name ?? null,
                    'rating' => $product->rating,
                    'reviews_count' => $product->reviews_count,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'count' => $items->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    public function add(Request $request, $productId)
    {
        try {
            $user = $request->user();

            $product = Product::find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $exists = Wishlist::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product already in wishlist'
                ], 400);
            }

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    public function remove(Request $request, $productId)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove from wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    public function check(Request $request, $productId)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check wishlist: ' . $e->getMessage()
            ], 500);
        }
    }
}