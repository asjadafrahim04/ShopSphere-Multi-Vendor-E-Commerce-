<?php

namespace App\Http\Controllers\Api;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    /**
     * Validate coupon
     */
    public function validate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'subtotal' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request',
                    'errors' => $validator->errors()
                ], 422);
            }

            $coupon = Coupon::where('code', strtoupper($request->code))
                ->where('is_active', true)
                ->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found or inactive'
                ], 404);
            }

            // Check if coupon is valid
            if (!$coupon->isValid($request->subtotal)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon is not valid'
                ], 400);
            }

            $calculation = $coupon->calculateDiscount($request->subtotal);

            return response()->json([
                'success' => true,
                'data' => [
                    'coupon' => $coupon,
                    'discount_amount' => $calculation['discount_amount'],
                    'final_total' => $calculation['final_total'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Coupon validation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate coupon'
            ], 500);
        }
    }

    /**
     * Apply coupon (increment usage count)
     */
    public function apply(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request'
                ], 422);
            }

            $coupon = Coupon::where('code', strtoupper($request->code))->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }

            if (!$coupon->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon is not active'
                ], 400);
            }

            $coupon->used_count += 1;
            $coupon->save();

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully',
                'data' => $coupon
            ]);

        } catch (\Exception $e) {
            Log::error('Apply coupon error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon'
            ], 500);
        }
    }

    /**
     * Get all coupons (Admin)
     */
    public function index(Request $request)
    {
        try {
            $coupons = Coupon::with('vendor')
                ->when($request->search, function($query, $search) {
                    return $query->where('code', 'LIKE', "%{$search}%")
                        ->orWhere('name', 'LIKE', "%{$search}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $coupons
            ]);

        } catch (\Exception $e) {
            Log::error('Coupons fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch coupons'
            ], 500);
        }
    }

    /**
     * Create coupon (Admin)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|unique:coupons|max:50',
                'name' => 'nullable|string|max:255',
                'type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric|min:0.01',
                'min_order_amount' => 'required|numeric|min:0',
                'max_discount_amount' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
                'vendor_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $coupon = Coupon::create([
                'code' => strtoupper($request->code),
                'name' => $request->name,
                'type' => $request->type,
                'value' => $request->value,
                'min_order_amount' => $request->min_order_amount,
                'max_discount_amount' => $request->max_discount_amount,
                'usage_limit' => $request->usage_limit,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'vendor_id' => $request->vendor_id,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Coupon created successfully',
                'data' => $coupon
            ], 201);

        } catch (\Exception $e) {
            Log::error('Coupon creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create coupon'
            ], 500);
        }
    }

    /**
     * Delete coupon (Admin)
     */
    public function destroy($id)
    {
        try {
            $coupon = Coupon::find($id);
            
            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }

            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Coupon deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete coupon'
            ], 500);
        }
    }
}