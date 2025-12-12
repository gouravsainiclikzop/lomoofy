<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Get dashboard statistics (AJAX JSON Response).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        $period = $request->get('period', 'week');
        
        // Calculate date range based on period
        $dateRange = $this->getDateRange($period);
        $previousDateRange = $this->getPreviousDateRange($period);
        
        // Current period stats
        $currentOrders = Order::whereBetween('created_at', $dateRange)
            ->whereNull('deleted_at');
        
        $totalSells = $currentOrders->sum('total_amount') ?? 0;
        $totalOrders = $currentOrders->count();
        $avgOrderValue = $totalOrders > 0 ? ($totalSells / $totalOrders) : 0;
        
        // Previous period stats for comparison
        $previousOrders = Order::whereBetween('created_at', $previousDateRange)
            ->whereNull('deleted_at');
        
        $previousTotalSells = $previousOrders->sum('total_amount') ?? 0;
        $deltaPercentage = null;
        
        if ($previousTotalSells > 0) {
            $deltaPercentage = (($totalSells - $previousTotalSells) / $previousTotalSells) * 100;
        } elseif ($totalSells > 0) {
            $deltaPercentage = 100; // 100% increase from 0
        }
        
        // Additional stats
        $totalCustomers = Customer::whereNull('deleted_at')->count();
        $totalProducts = Product::where('status', 'published')
            ->whereNull('deleted_at')
            ->count();
        $pendingOrders = Order::where('status', 'pending')
            ->whereNull('deleted_at')
            ->count();
        
        // Refund count (orders with status cancelled or refunded)
        $refundCount = Order::whereIn('status', ['cancelled', 'refunded'])
            ->whereNull('deleted_at')
            ->whereBetween('created_at', $dateRange)
            ->count();
        
        // Revenue growth calculation
        $revenueGrowth = null;
        if ($previousTotalSells > 0) {
            $revenueGrowth = (($totalSells - $previousTotalSells) / $previousTotalSells) * 100;
        } elseif ($totalSells > 0) {
            $revenueGrowth = 100; // 100% growth from 0
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_sales' => round($totalSells, 2),
                'total_orders' => $totalOrders,
                'avg_order_value' => round($avgOrderValue, 2),
                'total_customers' => $totalCustomers,
                'active_products' => $totalProducts,
                'refund_count' => $refundCount,
                'revenue_growth' => $revenueGrowth ? round($revenueGrowth, 1) : null,
                'delta_percentage' => $deltaPercentage ? round($deltaPercentage, 1) : null,
                'pending_orders' => $pendingOrders,
            ],
            'message' => 'Stats loaded successfully'
        ]);
    }

    /**
     * Get sales chart data with anomaly detection.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesChart(Request $request)
    {
        $period = $request->get('period', 'week');
        $cumulative = $request->get('cumulative', false);
        $dateRange = $this->getDateRange($period);
        
        // Build date format based on period
        $dateFormat = $this->getDateFormat($period);
        
        $salesData = Order::whereBetween('created_at', $dateRange)
            ->whereNull('deleted_at')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(total_amount) as avg_order_value')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        $labels = $salesData->pluck('period')->toArray();
        $sales = $salesData->pluck('total')->map(function($val) { return round($val, 2); })->toArray();
        $orders = $salesData->pluck('count')->toArray();
        $avgOrderValues = $salesData->pluck('avg_order_value')->map(function($val) { return round($val, 2); })->toArray();
        
        // Apply cumulative mode if requested
        if ($cumulative) {
            $cumulativeSales = [];
            $cumulativeOrders = [];
            $runningSales = 0;
            $runningOrders = 0;
            
            foreach ($sales as $index => $sale) {
                $runningSales += $sale;
                $runningOrders += $orders[$index];
                $cumulativeSales[] = round($runningSales, 2);
                $cumulativeOrders[] = $runningOrders;
            }
            
            $sales = $cumulativeSales;
            $orders = $cumulativeOrders;
        }
        
        // Anomaly detection using IQR (Interquartile Range) method
        $anomalies = $this->detectAnomalies($sales, $orders);
        
        // Format labels for better display
        $formattedLabels = $this->formatLabels($labels, $period);
        
        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $formattedLabels,
                'raw_labels' => $labels,
                'sales' => $sales,
                'orders' => $orders,
                'avg_order_values' => $avgOrderValues,
                'anomalies' => $anomalies,
                'data_points' => count($labels),
                'period' => $period,
            ]
        ]);
    }

    /**
     * Detect anomalies in sales and orders data.
     *
     * @param array $sales
     * @param array $orders
     * @return array
     */
    private function detectAnomalies($sales, $orders)
    {
        $anomalies = [];
        
        if (count($sales) < 3) {
            return $anomalies;
        }
        
        // Calculate IQR for sales
        $salesSorted = $sales;
        sort($salesSorted);
        $q1Sales = $this->percentile($salesSorted, 25);
        $q3Sales = $this->percentile($salesSorted, 75);
        $iqrSales = $q3Sales - $q1Sales;
        $lowerBoundSales = $q1Sales - (1.5 * $iqrSales);
        $upperBoundSales = $q3Sales + (1.5 * $iqrSales);
        
        // Calculate IQR for orders
        $ordersSorted = $orders;
        sort($ordersSorted);
        $q1Orders = $this->percentile($ordersSorted, 25);
        $q3Orders = $this->percentile($ordersSorted, 75);
        $iqrOrders = $q3Orders - $q1Orders;
        $lowerBoundOrders = $q1Orders - (1.5 * $iqrOrders);
        $upperBoundOrders = $q3Orders + (1.5 * $iqrOrders);
        
        // Detect anomalies
        foreach ($sales as $index => $sale) {
            $isAnomaly = false;
            $type = null;
            
            if ($sale < $lowerBoundSales || $sale > $upperBoundSales) {
                $isAnomaly = true;
                $type = $sale > $upperBoundSales ? 'high' : 'low';
            }
            
            if ($orders[$index] < $lowerBoundOrders || $orders[$index] > $upperBoundOrders) {
                $isAnomaly = true;
                if (!$type) {
                    $type = $orders[$index] > $upperBoundOrders ? 'high' : 'low';
                }
            }
            
            if ($isAnomaly) {
                $anomalies[] = [
                    'index' => $index,
                    'type' => $type,
                    'sales' => $sale,
                    'orders' => $orders[$index],
                ];
            }
        }
        
        return $anomalies;
    }

    /**
     * Calculate percentile.
     *
     * @param array $data
     * @param float $percentile
     * @return float
     */
    private function percentile($data, $percentile)
    {
        $index = ($percentile / 100) * (count($data) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        
        if ($lower == $upper) {
            return $data[$lower];
        }
        
        $weight = $index - $lower;
        return $data[$lower] * (1 - $weight) + $data[$upper] * $weight;
    }

    /**
     * Format labels based on period.
     *
     * @param array $labels
     * @param string $period
     * @return array
     */
    private function formatLabels($labels, $period)
    {
        $formatted = [];
        
        foreach ($labels as $label) {
            switch ($period) {
                case 'today':
                    $formatted[] = $label; // Already formatted as HH:00
                    break;
                case 'week':
                case '7day':
                    // Format as "Jan 15" or "Mon 15"
                    try {
                        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $label);
                        $formatted[] = $date->format('M j');
                    } catch (\Exception $e) {
                        $formatted[] = $label;
                    }
                    break;
                case 'month':
                case '30day':
                    try {
                        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $label);
                        $formatted[] = $date->format('M j');
                    } catch (\Exception $e) {
                        $formatted[] = $label;
                    }
                    break;
                case '90day':
                    try {
                        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $label);
                        $formatted[] = $date->format('M j');
                    } catch (\Exception $e) {
                        $formatted[] = $label;
                    }
                    break;
                case 'year':
                    // Format as "Jan 2024"
                    try {
                        $date = \Carbon\Carbon::createFromFormat('Y-m', $label);
                        $formatted[] = $date->format('M Y');
                    } catch (\Exception $e) {
                        $formatted[] = $label;
                    }
                    break;
                default:
                    $formatted[] = $label;
            }
        }
        
        return $formatted;
    }

    /**
     * Get orders by status chart data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersByStatus()
    {
        $statusData = Order::whereNull('deleted_at')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $statusData->pluck('status')->toArray(),
                'counts' => $statusData->pluck('count')->toArray(),
            ]
        ]);
    }

    /**
     * Get top products chart data with trends and images.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopProducts(Request $request)
    {
        $limit = $request->get('limit', 5);
        $viewType = $request->get('view_type', 'quantity'); // quantity, revenue, avg_price
        
        // Current period (last 30 days)
        $currentPeriodStart = Carbon::now()->subDays(30);
        $currentPeriodEnd = Carbon::now();
        
        // Previous period for comparison (30-60 days ago)
        $previousPeriodStart = Carbon::now()->subDays(60);
        $previousPeriodEnd = Carbon::now()->subDays(30);
        
        // Get current period top products
        $topProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('AVG(unit_price) as avg_price')
            )
            ->whereHas('order', function($query) use ($currentPeriodStart, $currentPeriodEnd) {
                $query->whereNull('deleted_at')
                    ->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd]);
            })
            ->groupBy('product_id')
            ->orderBy($viewType === 'revenue' ? 'total_revenue' : ($viewType === 'avg_price' ? 'avg_price' : 'total_quantity'), 'desc')
            ->limit($limit)
            ->with(['product:id,name', 'product.primaryImage'])
            ->get();
        
        // Get previous period data for trend calculation
        $previousPeriodData = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->whereHas('order', function($query) use ($previousPeriodStart, $previousPeriodEnd) {
                $query->whereNull('deleted_at')
                    ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd]);
            })
            ->whereIn('product_id', $topProducts->pluck('product_id'))
            ->groupBy('product_id')
            ->pluck('total_quantity', 'product_id')
            ->toArray();
        
        // Calculate totals for summary KPIs
        $totalUnitsSold = $topProducts->sum('total_quantity');
        $totalRevenue = $topProducts->sum('total_revenue');
        
        // Get overall sales for share calculation
        $overallSales = Order::whereNull('deleted_at')
            ->whereBetween('created_at', [$currentPeriodStart, $currentPeriodEnd])
            ->sum('total_amount');
        
        $shareOfSales = $overallSales > 0 ? ($totalRevenue / $overallSales) * 100 : 0;
        
        $products = $topProducts->map(function($item, $index) use ($previousPeriodData, $viewType) {
            $product = $item->product;
            $previousQuantity = $previousPeriodData[$item->product_id] ?? 0;
            $currentQuantity = $item->total_quantity;
            
            // Calculate MoM trend
            $momTrend = null;
            if ($previousQuantity > 0) {
                $momTrend = (($currentQuantity - $previousQuantity) / $previousQuantity) * 100;
            } elseif ($currentQuantity > 0) {
                $momTrend = 100; // 100% increase from 0
            }
            
            // Get product image
            $imageUrl = null;
            if ($product) {
                if ($product->primaryImage) {
                    $imageUrl = asset('storage/' . $product->primaryImage->image_path);
                } elseif ($product->image_url) {
                    $imageUrl = $product->image_url;
                }
            }
            
            return [
                'id' => $item->product_id,
                'name' => $product->name ?? 'Unknown',
                'quantity' => $item->total_quantity,
                'revenue' => round($item->total_revenue, 2),
                'avg_price' => round($item->avg_price ?? ($item->total_revenue / max($item->total_quantity, 1)), 2),
                'image' => $imageUrl,
                'mom_trend' => $momTrend ? round($momTrend, 1) : null,
                'rank' => $index + 1,
            ];
        })->toArray();
        
        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products,
                'summary' => [
                    'total_units_sold' => $totalUnitsSold,
                    'total_revenue' => round($totalRevenue, 2),
                    'share_of_sales' => round($shareOfSales, 1),
                ],
                'has_enough_data' => count($products) > 1,
            ]
        ]);
    }

    /**
     * Get recent orders (AJAX JSON Response).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentOrders(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $orders = Order::with('customer')
            ->whereNull('deleted_at')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->full_name ?? 'Guest',
                    'customer_id' => $order->customer_id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status ?? 'pending',
                    'date' => $order->created_at->format('Y-m-d'),
                    'date_formatted' => $order->created_at->format('M d, Y'),
                    'time' => $order->created_at->format('h:i A'),
                    'total' => $order->total_amount,
                    'item_count' => $order->items()->count(),
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders loaded successfully'
        ]);
    }

    /**
     * Get date range based on period.
     *
     * @param string $period
     * @return array
     */
    private function getDateRange($period)
    {
        $end = Carbon::now();
        
        switch($period) {
            case 'today':
                $start = Carbon::today();
                break;
            case 'week':
            case '7day':
                $start = Carbon::now()->subDays(7);
                break;
            case 'month':
            case '30day':
                $start = Carbon::now()->subDays(30);
                break;
            case '90day':
                $start = Carbon::now()->subDays(90);
                break;
            case 'year':
                $start = Carbon::now()->startOfYear();
                break;
            default:
                $start = Carbon::now()->subDays(7);
        }
        
        return [$start, $end];
    }

    /**
     * Get previous period date range for comparison.
     *
     * @param string $period
     * @return array
     */
    private function getPreviousDateRange($period)
    {
        $end = Carbon::now();
        
        switch($period) {
            case 'today':
                $start = Carbon::yesterday();
                $end = Carbon::yesterday()->endOfDay();
                break;
            case 'week':
                $start = Carbon::now()->subWeek()->startOfWeek();
                $end = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'year':
                $start = Carbon::now()->subYear()->startOfYear();
                $end = Carbon::now()->subYear()->endOfYear();
                break;
            default:
                $start = Carbon::now()->subWeek()->startOfWeek();
                $end = Carbon::now()->subWeek()->endOfWeek();
        }
        
        return [$start, $end];
    }

    /**
     * Get SQL date format for grouping.
     *
     * @param string $period
     * @return string
     */
    private function getGroupBy($period)
    {
        return $this->getDateFormat($period);
    }

    /**
     * Get MySQL date format string.
     *
     * @param string $period
     * @return string
     */
    private function getDateFormat($period)
    {
        switch($period) {
            case 'today':
                return '%H:00'; // Hourly
            case 'week':
            case '7day':
                return '%Y-%m-%d'; // Daily
            case 'month':
            case '30day':
                return '%Y-%m-%d'; // Daily
            case '90day':
                return '%Y-%m-%d'; // Daily
            case 'year':
                return '%Y-%m'; // Monthly
            default:
                return '%Y-%m-%d';
        }
    }
}

