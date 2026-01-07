<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Support\Str;


class ReviewController extends Controller
{
    /**
     * Store a new review for a product
     */
    public function store(Request $request)
    {
        // Check if customer is authenticated
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'You must be logged in to submit a review.'
                ]
            ], 401);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ]
            ], 422);
        }

        // Check if product exists and is published
        $product = Product::where('id', $request->product_id)
            ->where('status', 'published')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Product not found or not available.'
                ]
            ], 404);
        }

        // Check if customer has already reviewed this product
        $existingReview = Review::where('product_id', $request->product_id)
            ->where('user_id', $customer->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'You have already submitted a review for this product.'
                ]
            ], 422);
        }

        // Create the review
        try {
            $review = Review::create([
                'product_id' => $request->product_id,
                'user_id' => $customer->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'inactive', // New reviews are inactive by default, admin can activate
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. It will be visible after admin approval.',
                'data' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'status' => $review->status,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Failed to submit review. Please try again.'
                ]
            ], 500);
        }
    }

    /**
     * Get reviews for a specific product (only active reviews)
     */
    public function getProductReviews(Request $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Product not found.'
                ]
            ], 404);
        }

        $reviews = Review::where('product_id', $productId)
            ->where('status', 'active')
            ->with('customer:id,full_name,profile_image')
            ->orderBy('created_at', 'desc')
            ->get();

            $reviewsData = $reviews->map(function($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'customer_name' => $review->customer
                        ? Str::title($review->customer->full_name)
                        : 'Anonymous',
                    'customer_image' => $review->customer && $review->customer->profile_image
                        ? asset('storage/' . $review->customer->profile_image)
                        : null,
                    'created_at' => $review->created_at->format('M d, Y'),
                    'created_at_full' => $review->created_at->format('Y-m-d H:i:s'),
                ];
            });
            

        // Calculate average rating
        $averageRating = $reviews->count() > 0 
            ? round($reviews->avg('rating'), 1) 
            : 0;

        // Calculate rating distribution
        $ratingDistribution = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviewsData,
                'total_reviews' => $reviews->count(),
                'average_rating' => $averageRating,
                'rating_distribution' => $ratingDistribution,
            ]
        ]);
    }

    /**
     * Check if current customer can review a product
     */
    public function canReview(Request $request, $productId)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json([
                'success' => true,
                'data' => [
                    'can_review' => false,
                    'reason' => 'not_logged_in'
                ]
            ]);
        }

        $hasReviewed = Review::where('product_id', $productId)
            ->where('user_id', $customer->id)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'can_review' => !$hasReviewed,
                'has_reviewed' => $hasReviewed,
                'reason' => $hasReviewed ? 'already_reviewed' : 'eligible'
            ]
        ]);
    }
}
