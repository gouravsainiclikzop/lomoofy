<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Review;
use App\Models\Product;
use App\Models\Customer;

class AdminReviewController extends Controller
{
    /**
     * Display reviews management page
     */
    public function index()
    {
        // Get statistics
        $stats = [
            'total' => Review::count(),
            'active' => Review::where('status', 'active')->count(),
            'inactive' => Review::where('status', 'inactive')->count(),
        ];
        
        return view('admin.reviews.index', compact('stats'));
    }

    /**
     * Get reviews data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            // Build base query for counting (before any joins)
            $countQuery = Review::query();

            // Build query for data retrieval
            $query = Review::with(['product', 'customer']);

            // Search
            if ($request->has('search') && is_array($request->search) && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $searchCallback = function($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($q) use ($search) {
                          $q->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('product', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                };
                $query->where($searchCallback);
                $countQuery->where($searchCallback);
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $query->where('status', $request->status);
                $countQuery->where('status', $request->status);
            }

            // Filter by rating
            if ($request->has('rating') && $request->rating !== '' && $request->rating !== null) {
                $query->where('rating', $request->rating);
                $countQuery->where('rating', $request->rating);
            }

            // Ordering
            // DataTable columns: 0=checkbox, 1=customer_name, 2=product_name, 3=rating, 4=comment, 5=status, 6=created_at, 7=actions
            $orderColumn = 6; // Default to created_at
            $orderDir = 'desc';
            
            if ($request->has('order') && is_array($request->order) && count($request->order) > 0) {
                $orderColumn = $request->order[0]['column'] ?? 6;
                $orderDir = $request->order[0]['dir'] ?? 'desc';
            }
            
            // Map DataTable column indices to database columns
            $columnMap = [
                1 => 'customer_name',
                2 => 'product_name',
                3 => 'rating',
                4 => 'comment',
                5 => 'status',
                6 => 'created_at',
            ];
            
            $orderBy = $columnMap[$orderColumn] ?? 'created_at';
            
            // Apply ordering (use left joins to include records even if relationships are missing)
            if ($orderBy === 'customer_name') {
                $query->leftJoin('customers', 'reviews.user_id', '=', 'customers.id')
                      ->orderBy('customers.full_name', $orderDir)
                      ->select('reviews.*');
            } elseif ($orderBy === 'product_name') {
                $query->leftJoin('products', 'reviews.product_id', '=', 'products.id')
                      ->orderBy('products.name', $orderDir)
                      ->select('reviews.*');
            } else {
                $query->orderBy('reviews.' . $orderBy, $orderDir);
            }

            // Total count before pagination (calculated separately to avoid join issues)
            $totalRecords = $countQuery->count();

            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $reviews = $query->skip($start)->take($length)->get();

            $data = $reviews->map(function($review) {
                return [
                    'id' => $review->id,
                    'customer_name' => $review->customer->full_name ?? 'N/A',
                    'customer_email' => $review->customer->email ?? 'N/A',
                    'product_name' => $review->product->name ?? 'N/A',
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'status' => $review->status,
                    'created_at' => $review->created_at ? $review->created_at->format('Y-m-d H:i:s') : 'N/A',
                    'created_at_formatted' => $review->created_at ? $review->created_at->format('M d, Y') : 'N/A',
                ];
            });

            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => Review::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching reviews data: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'draw' => intval($request->draw ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading reviews: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update review status (activate/deactivate)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive'
        ]);

        $review = Review::findOrFail($id);
        $review->status = $request->status;
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Review status updated successfully.'
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.'
        ]);
    }

    /**
     * Bulk update review statuses
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id',
            'status' => 'required|in:active,inactive'
        ]);

        Review::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Reviews updated successfully.'
        ]);
    }

    /**
     * Bulk delete reviews
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id'
        ]);

        Review::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reviews deleted successfully.'
        ]);
    }
}
