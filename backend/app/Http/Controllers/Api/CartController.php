<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // ===== GET USER CART =====
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get or create cart
        $cart = $user->cart()->firstOrCreate([]);
        
        // Load cart items with product details
        $cart->load(['items.product', 'items.product.images', 'items.product.vendor']);
        
        // Calculate totals
        $items = $cart->items;
        $total = $items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        $count = $items->sum('quantity');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cart->id,
                'items' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product->name,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'total' => $item->quantity * $item->price,
                        'image' => $item->product->images->first()->image_url ?? null,
                        'vendor' => $item->product->vendor->shop_name ?? null,
                        'stock' => $item->product->stock_quantity,
                    ];
                }),
                'total' => $total,
                'count' => $count,
                'subtotal' => $total,
            ]
        ]);
    }

    // ===== ADD ITEM TO CART =====
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $product = Product::find($request->product_id);

        // Check if product exists and is active
        if (!$product || $product->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Product not available'
            ], 404);
        }

        // Check if product is in stock
        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available. Available: ' . $product->stock_quantity
            ], 400);
        }

        // Get or create cart
        $cart = $user->cart()->firstOrCreate([]);

        // Check if product already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $request->quantity;
            
            // Check stock
            if ($product->stock_quantity < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available. Available: ' . $product->stock_quantity
                ], 400);
            }
            
            $cartItem->quantity = $newQuantity;
            $cartItem->price = $product->price; // Update price in case it changed
            $cartItem->save();
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);
        }

        // Reload cart
        $cart->load(['items.product']);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => [
                'item' => $cartItem,
                'cart_count' => $cart->items->sum('quantity'),
                'cart_total' => $cart->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                })
            ]
        ], 201);
    }

    // ===== UPDATE CART ITEM =====
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem = CartItem::find($id);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        // Check if user owns this cart
        $user = $request->user();
        if ($cartItem->cart->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check stock
        $product = Product::find($cartItem->product_id);
        if ($product->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available. Available: ' . $product->stock_quantity
            ], 400);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        // Reload cart
        $cart = $cartItem->cart;
        $cart->load(['items.product']);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'data' => [
                'item' => $cartItem,
                'cart_count' => $cart->items->sum('quantity'),
                'cart_total' => $cart->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                })
            ]
        ]);
    }

    // ===== REMOVE ITEM FROM CART =====
    public function remove(Request $request, $id)
    {
        $cartItem = CartItem::find($id);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        // Check if user owns this cart
        $user = $request->user();
        if ($cartItem->cart->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $cartItem->delete();

        // Reload cart
        $cart = $user->cart()->first();
        if ($cart) {
            $cart->load(['items.product']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'data' => [
                'cart_count' => $cart ? $cart->items->sum('quantity') : 0,
                'cart_total' => $cart ? $cart->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                }) : 0
            ]
        ]);
    }

    // ===== CLEAR CART =====
    public function clear(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart()->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'data' => [
                'cart_count' => 0,
                'cart_total' => 0
            ]
        ]);
    }

    // ===== GET CART TOTAL =====
    public function total(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart()->first();

        if (!$cart) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => 0,
                    'count' => 0
                ]
            ]);
        }

        $cart->load(['items.product']);
        $total = $cart->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        $count = $cart->items->sum('quantity');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'count' => $count
            ]
        ]);
    }
}