<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ===== GET USER ORDERS =====
    public function index(Request $request)
    {
        $user = $request->user();
        
        $orders = Order::with(['items', 'items.product', 'vendor'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // ===== GET SINGLE ORDER =====
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $order = Order::with(['items', 'items.product', 'vendor', 'user'])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    // ===== CREATE ORDER =====
    public function store(Request $request)
    {
        $user = $request->user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|array',
            'shipping_address.full_name' => 'required|string',
            'shipping_address.email' => 'required|email',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.district' => 'required|string',
            'shipping_address.delivery_area' => 'required|string',
            'payment_method' => 'required|in:bkash,nagad,rocket,cod,card',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get user's cart
        $cart = $user->cart()->first();
        if (!$cart || $cart->items->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        // Calculate totals
        $subtotal = 0;
        $items = [];

        DB::beginTransaction();

        try {
            foreach ($cart->items as $cartItem) {
                $product = Product::find($cartItem->product_id);
                
                if (!$product) {
                    throw new \Exception("Product not found: {$cartItem->product_id}");
                }

                // Check stock
                if (!$product->hasStock($cartItem->quantity)) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                // Calculate item total
                $price = $product->discounted_price ?? $product->price;
                $itemTotal = $price * $cartItem->quantity;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $price,
                    'total' => $itemTotal,
                ];

                $subtotal += $itemTotal;

                // Decrease stock
                $product->decreaseStock($cartItem->quantity);
            }

            // Calculate shipping cost based on delivery area
            $shippingCost = $this->calculateShipping($request->shipping_address['delivery_area'], $subtotal);

            // Calculate tax (8%)
            $tax = $subtotal * 0.08;

            // Calculate total
            $total = $subtotal + $shippingCost + $tax;

            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(uniqid()) . '-' . time();

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'vendor_id' => null,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'discount' => 0,
                'total' => $total,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                ]);
            }

            // Clear the cart
            $cart->items()->delete();

            DB::commit();

            // Load order with items
            $order->load(['items', 'items.product']);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => $order,
                    'order_number' => $orderNumber,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== CANCEL ORDER =====
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        
        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if order can be cancelled
        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Restore stock
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increaseStock($item->quantity);
                }
            }

            // Update order status
            $order->status = 'cancelled';
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== TRACK ORDER =====
    public function track(Request $request, $id)
    {
        $user = $request->user();
        
        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Order tracking steps
        $trackingSteps = [
            'pending' => [
                'status' => 'Order Placed',
                'description' => 'Your order has been placed successfully.',
                'icon' => 'check-circle',
                'completed' => true,
            ],
            'processing' => [
                'status' => 'Processing',
                'description' => 'Your order is being processed.',
                'icon' => 'clock',
                'completed' => in_array($order->status, ['processing', 'shipped', 'delivered']),
            ],
            'shipped' => [
                'status' => 'Shipped',
                'description' => 'Your order has been shipped.',
                'icon' => 'truck',
                'completed' => in_array($order->status, ['shipped', 'delivered']),
            ],
            'delivered' => [
                'status' => 'Delivered',
                'description' => 'Your order has been delivered.',
                'icon' => 'check-all',
                'completed' => $order->status === 'delivered',
            ],
        ];

        // Get current step index
        $currentStepIndex = array_search($order->status, array_keys($trackingSteps));
        if ($order->status === 'cancelled') {
            $currentStepIndex = -1;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'tracking' => $trackingSteps,
                'current_step' => $currentStepIndex,
            ]
        ]);
    }

    // ===== CALCULATE SHIPPING COST =====
    private function calculateShipping($deliveryArea, $subtotal)
    {
        // Free shipping for orders over $100
        if ($subtotal > 100) {
            return 0;
        }

        $shippingRates = [
            'Inside Dhaka' => 60,
            'Outside Dhaka' => 120,
            'Other City' => 100,
        ];

        return $shippingRates[$deliveryArea] ?? 60;
    }
}