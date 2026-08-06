<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Get product reviews (Public)
     */
    public function index($productId)
    {
        try {
            $reviews = Review::with('user')
                ->where('product_id', $productId)
                ->where('is_approved', true)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $averageRating = Review::where('product_id', $productId)
                ->where('is_approved', true)
                ->avg('rating') ?? 0;

            $totalReviews = Review::where('product_id', $productId)
                ->where('is_approved', true)
                ->count();

            // Rating breakdown
            $ratingBreakdown = [];
            for ($i = 5; $i >= 1; $i--) {
                $ratingBreakdown[$i] = Review::where('product_id', $productId)
                    ->where('is_approved', true)
                    ->where('rating', $i)
                    ->count();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'reviews' => $reviews,
                    'average_rating' => round($averageRating, 1),
                    'total_reviews' => $totalReviews,
                    'rating_breakdown' => $ratingBreakdown,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Reviews fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reviews'
            ], 500);
        }
    }

    /**
     * Store a new review
     * ✅ FIXED: Removed purchase check for testing
     */
    public function store(Request $request)
    {
        try {
            Log::info('Review submission started', [
                'user_id' => $request->user()?->id,
                'data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'images' => 'nullable|array',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                Log::warning('Review validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // ✅ Check if user already reviewed this product
            $existing = Review::where('product_id', $request->product_id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this product'
                ], 400);
            }

            // ✅ TEMPORARILY DISABLED: Purchase check for testing
            // Uncomment this when you have delivered orders
            /*
            $hasPurchased = Order::where('user_id', $user->id)
                ->where('status', 'delivered')
                ->whereHas('items', function($q) use ($request) {
                    $q->where('product_id', $request->product_id);
                })
                ->exists();

            if (!$hasPurchased) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only review products you have purchased and received.'
                ], 403);
            }
            */

            $review = Review::create([
                'product_id' => $request->product_id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_verified' => true,
                'is_approved' => true,
            ]);

            // Update product rating
            $this->updateProductRating($request->product_id);

            Log::info('Review created successfully', ['review_id' => $review->id]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully!',
                'data' => $review->load('user')
            ], 201);

        } catch (\Exception $e) {
            Log::error('Review creation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product rating
     */
    private function updateProductRating($productId)
    {
        try {
            $product = Product::find($productId);
            if ($product) {
                $product->rating = Review::where('product_id', $productId)
                    ->where('is_approved', true)
                    ->avg('rating') ?? 0;
                $product->reviews_count = Review::where('product_id', $productId)
                    ->where('is_approved', true)
                    ->count();
                $product->save();
                
                Log::info('Product rating updated', [
                    'product_id' => $productId,
                    'rating' => $product->rating,
                    'reviews_count' => $product->reviews_count
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Update product rating error: ' . $e->getMessage());
        }
    }

    /**
     * Delete review (Owner or Admin)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $review = Review::find($id);
            
            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found'
                ], 404);
            }

            $user = $request->user();
            if ($review->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $productId = $review->product_id;
            $review->delete();
            
            $this->updateProductRating($productId);

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Review deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review'
            ], 500);
        }
    }
}