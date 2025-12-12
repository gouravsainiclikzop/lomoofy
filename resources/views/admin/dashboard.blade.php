@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="py-5">
        <div class="row g-4">
            <div class="col">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                <h1 class="h3 m-0">Dashboard</h1>
                        <p class="text-muted mb-0">Welcome back! Here's what's happening with your store today.</p>
            </div>
                    <div class="d-flex gap-2">
                        <select class="form-select" id="dateFilter" style="width: auto;">
                    <option value="today">Today</option>
                    <option value="week" selected>This Week</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
        </div>
    </div>
    
                <!-- KPI Summary Cards -->
                <div class="row g-4 mb-4">
                    <!-- Total Sales -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-muted mb-1 small text-uppercase">Total Sales</h6>
                                        <h3 class="mb-0" id="totalSales">₹0.00</h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 rounded p-2">
                                        <i class='bx bx-dollar-circle text-primary' style='font-size: 1.75rem;'></i>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted" id="salesDelta" style="display:none;">
                                        <span id="salesDeltaValue" class="fw-bold">0%</span>
                                        <span class="ms-1">vs previous period</span>
                                    </small>
                                </div>
                </div>
                        </div>
                    </div>

                    <!-- Total Orders -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-muted mb-1 small text-uppercase">Total Orders</h6>
                                        <h3 class="mb-0" id="totalOrders">0</h3>
                                    </div>
                                    <div class="bg-info bg-opacity-10 rounded p-2">
                                        <i class='bx bx-shopping-bag text-info' style='font-size: 1.75rem;'></i>
                                    </div>
                                </div>
                                <small class="text-muted">Current Period</small>
                </div>
            </div>
        </div>
        
        <!-- Average Order Value -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-muted mb-1 small text-uppercase">Avg Order Value</h6>
                                        <h3 class="mb-0" id="avgOrderValue">₹0.00</h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 rounded p-2">
                                        <i class='bx bx-trending-up text-success' style='font-size: 1.75rem;'></i>
                                    </div>
                                </div>
                                <small class="text-muted">Per Order</small>
                            </div>
                        </div>
                    </div>

                    <!-- Total Customers -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-muted mb-1 small text-uppercase">Total Customers</h6>
                                        <h3 class="mb-0" id="totalCustomers">0</h3>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 rounded p-2">
                                        <i class='bx bx-user text-warning' style='font-size: 1.75rem;'></i>
                                    </div>
                                </div>
                                <small class="text-muted">Registered</small>
                            </div>
                        </div>
                    </div>

                    <!-- Active Products -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-muted mb-1 small text-uppercase">Active Products</h6>
                                        <h3 class="mb-0" id="activeProducts">0</h3>
                                    </div>
                                    <div class="bg-secondary bg-opacity-10 rounded p-2">
                                        <i class='bx bx-package text-secondary' style='font-size: 1.75rem;'></i>
                                    </div>
                                </div>
                                <small class="text-muted">In Stock</small>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Count -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-muted mb-1 small text-uppercase">Refunds</h6>
                                        <h3 class="mb-0" id="refundCount">0</h3>
                                    </div>
                                    <div class="bg-danger bg-opacity-10 rounded p-2">
                                        <i class='bx bx-undo text-danger' style='font-size: 1.75rem;'></i>
                                    </div>
                                </div>
                                <small class="text-muted">Current Period</small>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Growth -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-muted mb-1 small text-uppercase">Revenue Growth</h6>
                                        <h3 class="mb-0" id="revenueGrowth">0%</h3>
                                    </div>
                                    <div class="bg-success bg-opacity-10 rounded p-2">
                                        <i class='bx bx-line-chart text-success' style='font-size: 1.75rem;'></i>
                                    </div>
                                </div>
                                <small class="text-muted">vs Previous Period</small>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Orders -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="text-muted mb-1 small text-uppercase">Pending Orders</h6>
                                        <h3 class="mb-0" id="pendingOrders">0</h3>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 rounded p-2">
                                        <i class='bx bx-time text-warning' style='font-size: 1.75rem;'></i>
                                    </div>
                                </div>
                                <small class="text-muted">Requires Action</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <!-- Enhanced Sales Trend Chart -->
                    <div class="col-12 col-lg-8">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <h5 class="card-title mb-0">Sales Trend Analysis</h5>
                                    <div class="d-flex gap-2 flex-wrap align-items-center">
                                        <!-- Period Toggle Buttons -->
                                        <div class="btn-group btn-group-sm" role="group" id="periodToggleGroup">
                                            <input type="radio" class="btn-check" name="periodToggle" id="period7day" value="7day" autocomplete="off">
                                            <label class="btn btn-outline-primary" for="period7day">7D</label>
                                            
                                            <input type="radio" class="btn-check" name="periodToggle" id="period30day" value="30day" autocomplete="off" checked>
                                            <label class="btn btn-outline-primary" for="period30day">30D</label>
                                            
                                            <input type="radio" class="btn-check" name="periodToggle" id="period90day" value="90day" autocomplete="off">
                                            <label class="btn btn-outline-primary" for="period90day">90D</label>
                                            
                                            <input type="radio" class="btn-check" name="periodToggle" id="periodYear" value="year" autocomplete="off">
                                            <label class="btn btn-outline-primary" for="periodYear">1Y</label>
                                        </div>
                                        
                                        <!-- Refresh Button -->
                                        <button class="btn btn-sm btn-outline-secondary" id="refreshSalesChart" title="Refresh Chart">
                                            <i class='bx bx-refresh'></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body position-relative">
                                <!-- Anomaly Legend -->
                                <div id="anomalyLegend" class="mb-3" style="display: none;">
                                    <small class="text-muted">
                                        <i class='bx bx-info-circle'></i> 
                                        <span class="text-warning"><i class='bx bx-up-arrow-alt'></i> High</span> | 
                                        <span class="text-info"><i class='bx bx-down-arrow-alt'></i> Low</span> 
                                        anomalies detected
                                    </small>
                                </div>
                                <canvas id="salesChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
                    <!-- Orders by Status Chart -->
                    <div class="col-12 col-lg-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Orders by Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="ordersStatusChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products and Recent Orders -->
                <div class="row g-4">
                    <!-- Enhanced Top Products Chart -->
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Top Products</h5>
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select form-select-sm" id="topProductsLimit" style="width: auto;">
                                        <option value="5" selected>Top 5</option>
                                        <option value="10">Top 10</option>
                                        <option value="20">Top 20</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-secondary" id="refreshTopProducts" title="Refresh">
                                        <i class='bx bx-refresh'></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Metric Toggle Pills -->
                                <div class="d-flex justify-content-center mb-4">
                                    <div class="btn-group" role="group" id="topProductsViewToggle">
                                        <input type="radio" class="btn-check" name="topProductsView" id="viewQuantity" value="quantity" autocomplete="off" checked>
                                        <label class="btn btn-outline-primary btn-sm px-3" for="viewQuantity">Quantity</label>
                                        
                                        <input type="radio" class="btn-check" name="topProductsView" id="viewRevenue" value="revenue" autocomplete="off">
                                        <label class="btn btn-outline-primary btn-sm px-3" for="viewRevenue">Revenue</label>
                                        
                                        <input type="radio" class="btn-check" name="topProductsView" id="viewAvgPrice" value="avg_price" autocomplete="off">
                                        <label class="btn btn-outline-primary btn-sm px-3" for="viewAvgPrice">Avg Price</label>
                                    </div>
                                </div>
                                
                                <!-- Not Enough Data Message -->
                                <div id="notEnoughDataMessage" class="text-center py-5" style="display: none;">
                                    <i class='bx bx-info-circle text-muted' style='font-size: 2rem;'></i>
                                    <p class="text-muted mt-2 mb-0">Not enough data to show comparative ranking</p>
                                </div>
                                
                                <!-- Chart Container -->
                                <div id="topProductsChartContainer">
                                    <canvas id="topProductsChart" height="300"></canvas>
                                </div>
                                
                                <!-- Summary KPIs -->
                                <div id="topProductsSummary" class="mt-4 pt-3 border-top" style="display: none;">
                                    <div class="row g-3 text-center">
                                        <div class="col-4">
                                            <div class="d-flex flex-column">
                                                <span class="text-muted small mb-1">Total Units Sold</span>
                                                <span class="fw-bold" id="summaryTotalUnits">0</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex flex-column">
                                                <span class="text-muted small mb-1">Total Revenue</span>
                                                <span class="fw-bold text-success" id="summaryTotalRevenue">₹0.00</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="d-flex flex-column">
                                                <span class="text-muted small mb-1">Share of Sales</span>
                                                <span class="fw-bold text-primary" id="summaryShareOfSales">0%</span>
                                            </div>
                </div>
            </div>
        </div>
        
                                <!-- View All Products Button -->
                                <div class="text-center mt-4">
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm px-4">
                                        View All Products
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Recent Orders Table -->
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Recent Orders</h5>
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select form-select-sm" id="recentOrdersLimit" style="width: auto;">
                                        <option value="5" selected>Last 5</option>
                                        <option value="10">Last 10</option>
                                        <option value="15">Last 15</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary" id="refreshOrders" title="Refresh">
                                        <i class='bx bx-refresh'></i>
                                    </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                                <th style="width: 30%;">Order #</th>
                                                <th style="width: 30%;">Customer</th>
                                                <th style="width: 20%;">Amount</th>
                                                <th style="width: 20%;">Date</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <tr>
                                                <td colspan="4" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary">
                                        <i class='bx bx-right-arrow-alt'></i> View All Orders
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Ensure Sales Trend and Orders by Status cards have equal height */
    .row.g-4 > .col-12.col-lg-8 .card,
    .row.g-4 > .col-12.col-lg-4 .card {
        display: flex;
        flex-direction: column;
    }
    .row.g-4 > .col-12.col-lg-8 .card .card-body,
    .row.g-4 > .col-12.col-lg-4 .card .card-body {
        display: flex;
        flex-direction: column;
    }
    #salesChart,
    #ordersStatusChart {
        width: 100% !important;
        height: auto !important;
    }
</style>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.15s ease-in-out;
        border-radius: 0.5rem;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #495057;
        margin: 0;
    }
    .card-body h3 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #212529;
    }
    .card-body h6 {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .bg-opacity-10 {
        opacity: 0.1;
    }
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        color: #6c757d;
        border-bottom: 2px solid #dee2e6;
    }
    .table td {
        vertical-align: middle;
    }
    
    /* Top Products Enhancements */
    .product-thumbnail {
        width: 28px;
        height: 28px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 8px;
        display: inline-block;
        vertical-align: middle;
    }
    
    .product-thumbnail-placeholder {
        width: 28px;
        height: 28px;
        background-color: #f3f4f6;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 8px;
        color: #9ca3af;
        font-size: 14px;
    }
    
    .product-label {
        display: inline-block;
        vertical-align: middle;
        margin-right: 8px;
    }
    
    .rank-badge {
        display: inline-block;
        margin-right: 8px;
        font-size: 16px;
        vertical-align: middle;
    }
    
    .rank-number {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
    }
    
    .trend-indicator {
        display: inline-block;
        font-size: 10px;
        font-weight: 600;
        margin-left: 6px;
        padding: 2px 6px;
        border-radius: 4px;
        vertical-align: middle;
    }
    
    .trend-up {
        color: #10b981;
        background-color: rgba(16, 185, 129, 0.1);
    }
    
    .trend-down {
        color: #ef4444;
        background-color: rgba(239, 68, 68, 0.1);
    }
    
    #topProductsChartContainer {
        min-height: 300px;
        padding: 20px 0;
    }
    
    #topProductsSummary {
        background-color: #f9fafb;
        border-radius: 8px;
        padding: 16px;
    }
    
    /* Enhanced Chart Styles */
    #salesChart {
        max-height: 400px;
    }
    
    .btn-group-sm .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        font-weight: 500;
    }
    
    .btn-check:checked + .btn-outline-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-header .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .btn-group {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .btn-group .btn {
            flex: 1;
        }
        
        #salesChart {
            max-height: 300px;
        }
    }
    
    @media (max-width: 576px) {
        #salesChart {
            max-height: 250px;
        }
    }
    
    /* Top Products Enhancements */
    .list-group-item {
        border-left: none;
        border-right: none;
        padding: 0.75rem 1rem;
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    .badge-sm {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
    }
    
    /* Recent Orders Table Enhancements */
    .order-row:hover {
        background-color: #f8f9fa;
    }
    
    .order-row td {
        padding: 0.75rem 0.5rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .table th {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c7280;
        padding: 0.75rem 0.5rem;
    }
    
    /* Form Select Small */
    .form-select-sm {
        font-size: 0.75rem;
        padding: 0.25rem 1.75rem 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Chart instances
    let salesChart = null;
    let ordersStatusChart = null;
    let topProductsChart = null;

    // Chart color palette
    const colors = {
        primary: '#0d6efd',
        success: '#00a629',
        info: '#0dcaf0',
        warning: '#ffc107',
        danger: '#dc3545',
        secondary: '#6c757d',
        light: '#f8f9fa',
    };

    // Status color mapping
    const statusColors = {
        'pending': colors.warning,
        'processing': colors.info,
        'confirmed': colors.primary,
        'shipped': colors.secondary,
        'delivered': colors.success,
        'completed': colors.success,
        'cancelled': colors.danger,
        'refunded': colors.danger,
        'hold': colors.secondary,
    };

    /**
     * Load Dashboard Statistics
     */
    function loadDashboardStats(period = 'week') {
        $.ajax({
            url: '{{ route("dashboard.stats") }}',
            type: 'GET',
            data: { period: period },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    const data = response.data;
                    
                    // Update all KPI cards
                    $('#totalSales').text('₹' + parseFloat(data.total_sales || 0).toFixed(2));
                    $('#totalOrders').text(data.total_orders || 0);
                    $('#avgOrderValue').text('₹' + parseFloat(data.avg_order_value || 0).toFixed(2));
                    $('#totalCustomers').text(data.total_customers || 0);
                    $('#activeProducts').text(data.active_products || 0);
                    $('#refundCount').text(data.refund_count || 0);
                    $('#pendingOrders').text(data.pending_orders || 0);
                    
                    // Revenue Growth
                    if(data.revenue_growth !== null) {
                        const growthClass = data.revenue_growth >= 0 ? 'text-success' : 'text-danger';
                        const growthIcon = data.revenue_growth >= 0 ? '+' : '';
                        $('#revenueGrowth').text(growthIcon + data.revenue_growth + '%').removeClass('text-success text-danger').addClass(growthClass);
                    } else {
                        $('#revenueGrowth').text('0%').removeClass('text-success text-danger');
                    }
                    
                    // Sales Delta
                    if(data.delta_percentage !== null) {
                        const deltaClass = data.delta_percentage >= 0 ? 'text-success' : 'text-danger';
                        const deltaIcon = data.delta_percentage >= 0 ? '↑' : '↓';
                        $('#salesDeltaValue').html(`<span class="${deltaClass}">${deltaIcon} ${Math.abs(data.delta_percentage)}%</span>`);
                        $('#salesDelta').show();
                    } else {
                        $('#salesDelta').hide();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading stats:', xhr);
            }
        });
    }

    /**
     * Load Enhanced Sales Trend Chart
     */
    function loadSalesChart(period = '30day') {
        $.ajax({
            url: '{{ route("dashboard.sales-chart") }}',
            type: 'GET',
            data: { 
                period: period,
                cumulative: 0
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    const ctx = document.getElementById('salesChart');
                    if(!ctx) return;
                    
                    const data = response.data;
                    const dataPoints = data.data_points || data.sales.length;
                    
                    // Determine chart type: bar if < 3 data points, line otherwise
                    const chartType = dataPoints < 3 ? 'bar' : 'line';
                    
                    // Show/hide anomaly legend
                    if(data.anomalies && data.anomalies.length > 0) {
                        $('#anomalyLegend').show();
                    } else {
                        $('#anomalyLegend').hide();
                    }
                    
                    // Prepare point colors for anomalies
                    const salesPointColors = data.sales.map((val, index) => {
                        if(data.anomalies) {
                            const anomaly = data.anomalies.find(a => a.index === index);
                            if(anomaly) {
                                return anomaly.type === 'high' ? '#f59e0b' : '#3b82f6';
                            }
                        }
                        return '#2563eb';
                    });
                    
                    const salesPointBorderColors = data.sales.map((val, index) => {
                        if(data.anomalies) {
                            const anomaly = data.anomalies.find(a => a.index === index);
                            if(anomaly) {
                                return anomaly.type === 'high' ? '#d97706' : '#2563eb';
                            }
                        }
                        return '#2563eb';
                    });
                    
                    const salesPointBorderWidths = data.sales.map((val, index) => {
                        if(data.anomalies) {
                            const anomaly = data.anomalies.find(a => a.index === index);
                            if(anomaly) return 3;
                        }
                        return 2;
                    });
                    
                    const ordersPointColors = data.orders.map((val, index) => {
                        if(data.anomalies) {
                            const anomaly = data.anomalies.find(a => a.index === index);
                            if(anomaly) {
                                return anomaly.type === 'high' ? '#f59e0b' : '#10b981';
                            }
                        }
                        return '#10b981';
                    });
                    
                    const ordersPointBorderColors = data.orders.map((val, index) => {
                        if(data.anomalies) {
                            const anomaly = data.anomalies.find(a => a.index === index);
                            if(anomaly) {
                                return anomaly.type === 'high' ? '#d97706' : '#059669';
                            }
                        }
                        return '#10b981';
                    });
                    
                    const ordersPointBorderWidths = data.orders.map((val, index) => {
                        if(data.anomalies) {
                            const anomaly = data.anomalies.find(a => a.index === index);
                            if(anomaly) return 3;
                        }
                        return 2;
                    });
                    
                    // Prepare datasets with anomaly indicators
                    const salesDataset = {
                        label: 'Sales (₹)',
                        data: data.sales,
                        borderColor: '#2563eb', // Enhanced blue for better contrast
                        backgroundColor: chartType === 'bar' ? 'rgba(37, 99, 235, 0.8)' : 'rgba(37, 99, 235, 0.1)',
                        tension: chartType === 'line' ? 0.4 : 0,
                        fill: chartType === 'line',
                        yAxisID: 'y',
                        pointRadius: chartType === 'line' ? 4 : 0,
                        pointHoverRadius: 6,
                        pointBackgroundColor: salesPointColors,
                        pointBorderColor: salesPointBorderColors,
                        pointBorderWidth: salesPointBorderWidths,
                        borderWidth: 2.5,
                        maxBarThickness: chartType === 'bar' ? 24 : undefined, // Maximum bar height to prevent stretching - same as Top Products
                    };
                    
                    const ordersDataset = {
                        label: 'Orders',
                        data: data.orders,
                        borderColor: '#10b981', // Enhanced green for better contrast
                        backgroundColor: chartType === 'bar' ? 'rgba(16, 185, 129, 0.8)' : 'rgba(16, 185, 129, 0.1)',
                        tension: chartType === 'line' ? 0.4 : 0,
                        fill: chartType === 'line',
                        yAxisID: 'y1',
                        pointRadius: chartType === 'line' ? 4 : 0,
                        pointHoverRadius: 6,
                        pointBackgroundColor: ordersPointColors,
                        pointBorderColor: ordersPointBorderColors,
                        pointBorderWidth: ordersPointBorderWidths,
                        borderWidth: 2.5,
                        maxBarThickness: chartType === 'bar' ? 24 : undefined, // Maximum bar height to prevent stretching - same as Top Products
                    };
                    
                    const chartConfig = {
                        type: chartType,
                        data: {
                            labels: data.labels,
                            datasets: [salesDataset, ordersDataset]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 2,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 15,
                                        font: { size: 12, weight: '500' },
                                        color: '#374151'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                    padding: 14,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    borderColor: 'rgba(255, 255, 255, 0.1)',
                                    borderWidth: 1,
                                    cornerRadius: 8,
                                    displayColors: true,
                                    callbacks: {
                                        title: function(context) {
                                            return context[0].label;
                                        },
                                        label: function(context) {
                                            const datasetLabel = context.dataset.label || '';
                                            const value = context.parsed.y;
                                            
                                            if(context.datasetIndex === 0) {
                                                // Sales dataset
                                                const avgValue = data.avg_order_values && data.avg_order_values[context.dataIndex] 
                                                    ? data.avg_order_values[context.dataIndex] 
                                                    : (value / (data.orders[context.dataIndex] || 1));
                                                
                                                return [
                                                    datasetLabel + ': ₹' + parseFloat(value).toFixed(2),
                                                    'Orders: ' + data.orders[context.dataIndex],
                                                    'Avg Order Value: ₹' + parseFloat(avgValue).toFixed(2)
                                                ];
                                            } else {
                                                // Orders dataset
                                                return datasetLabel + ': ' + value;
                                            }
                                        },
                                        afterLabel: function(context) {
                                            // Show anomaly indicator in tooltip
                                            if(data.anomalies) {
                                                const anomaly = data.anomalies.find(a => a.index === context.dataIndex);
                                                if(anomaly) {
                                                    const icon = anomaly.type === 'high' ? '↑' : '↓';
                                                    const color = anomaly.type === 'high' ? '#f59e0b' : '#3b82f6';
                                                    return '\n' + icon + ' Anomaly detected';
                                                }
                                            }
                                            return '';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    categoryPercentage: chartType === 'bar' ? 0.6 : undefined, // Controls spacing between categories (bars) - same as Top Products
                                    barPercentage: chartType === 'bar' ? 0.5 : undefined, // Controls bar width within category - same as Top Products
                                    grid: {
                                        display: false,
                                        color: 'rgba(0, 0, 0, 0.05)',
                                    },
                                    ticks: {
                                        font: { size: 11, weight: '500' },
                                        color: '#6b7280',
                                        maxRotation: 45,
                                        minRotation: 0
                                    },
                                    border: {
                                        display: false
                                    }
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)',
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            if(value >= 1000) {
                                                return '₹' + (value / 1000).toFixed(1) + 'k';
                                            }
                                            return '₹' + value.toFixed(0);
                                        },
                                        font: { size: 11, weight: '500' },
                                        color: '#2563eb',
                                        padding: 8
                                    },
                                    title: {
                                        display: true,
                                        text: 'Sales (₹)',
                                        font: { size: 12, weight: '600' },
                                        color: '#2563eb',
                                        padding: { bottom: 10 }
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    beginAtZero: true,
                                    grid: {
                                        drawOnChartArea: false,
                                        drawBorder: false,
                                    },
                                    ticks: {
                                        stepSize: dataPoints < 10 ? 1 : Math.ceil(Math.max(...data.orders) / 5),
                                        font: { size: 11, weight: '500' },
                                        color: '#10b981',
                                        padding: 8
                                    },
                                    title: {
                                        display: true,
                                        text: 'Orders',
                                        font: { size: 12, weight: '600' },
                                        color: '#10b981',
                                        padding: { bottom: 10 }
                                    }
                                }
                            }
                        }
                    };
                    
                    if(salesChart) {
                        salesChart.destroy();
                    }
                    
                    salesChart = new Chart(ctx, chartConfig);
                }
            },
            error: function(xhr) {
                console.error('Error loading sales chart:', xhr);
            }
        });
    }

    /**
     * Load Orders by Status Chart
     */
    function loadOrdersByStatusChart() {
        $.ajax({
            url: '{{ route("dashboard.orders-by-status") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    const ctx = document.getElementById('ordersStatusChart');
                    if(!ctx) return;
                    
                    const labels = response.data.labels.map(l => l.charAt(0).toUpperCase() + l.slice(1));
                    const backgroundColors = response.data.labels.map(label => {
                        return statusColors[label.toLowerCase()] || colors.secondary;
                    });
                    
                    if(ordersStatusChart) {
                        ordersStatusChart.data.labels = labels;
                        ordersStatusChart.data.datasets[0].data = response.data.counts;
                        ordersStatusChart.data.datasets[0].backgroundColor = backgroundColors;
                        ordersStatusChart.update('active');
                    } else {
                        ordersStatusChart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: response.data.counts,
                                    backgroundColor: backgroundColors,
                                    borderWidth: 3,
                                    borderColor: '#fff',
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 1.5,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 12,
                                            font: { size: 12 }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        padding: 12,
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.parsed || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return label + ': ' + value + ' (' + percentage + '%)';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading orders status chart:', xhr);
            }
        });
    }

    /**
     * Load Enhanced Top Products Chart with all upgrades
     */
    function loadTopProductsChart() {
        const limit = $('#topProductsLimit').val() || 5;
        const viewType = $('input[name="topProductsView"]:checked').val() || 'quantity';
        
        $.ajax({
            url: '{{ route("dashboard.top-products") }}',
            type: 'GET',
            data: { 
                limit: limit,
                view_type: viewType
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    const ctx = document.getElementById('topProductsChart');
                    if(!ctx) return;
                    
                    if(response.data.products && response.data.products.length > 0) {
                        const products = response.data.products;
                        const labels = products.map(p => {
                            const name = p.name || 'Unknown';
                            return name.length > 20 ? name.substring(0, 20) + '...' : name;
                        });
                        
                        // Determine data based on view type
                        const dataValues = viewType === 'revenue' 
                            ? products.map(p => parseFloat(p.revenue || 0))
                            : products.map(p => p.quantity);
                        
                        const dataLabel = viewType === 'revenue' ? 'Revenue (₹)' : 'Quantity Sold';
                        const backgroundColor = viewType === 'revenue' 
                            ? 'rgba(16, 185, 129, 0.8)' 
                            : 'rgba(37, 99, 235, 0.8)';
                        const borderColor = viewType === 'revenue' 
                            ? '#10b981' 
                            : '#2563eb';
                        
                        // Update or create chart
                        if(topProductsChart) {
                            topProductsChart.destroy();
                            topProductsChart = null;
                        }
                        
                        // Always create new chart to ensure proper bar sizing
                        {
                            topProductsChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: dataLabel,
                                        data: dataValues,
                                        backgroundColor: backgroundColor,
                                        borderColor: borderColor,
                                        borderWidth: 1.5,
                                        borderRadius: 4,
                                        maxBarThickness: 24, // Maximum bar height to prevent stretching - reduced for thinner bars
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    indexAxis: 'y',
                                    layout: {
                                        padding: {
                                            left: 0,
                                            right: 0,
                                            top: 10,
                                            bottom: 10
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'top',
                                            labels: {
                                                font: { size: 12, weight: '500' },
                                                usePointStyle: true,
                                                padding: 15
                                            }
                                        },
                                        tooltip: {
                                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                            padding: 14,
                                            titleFont: { size: 14, weight: 'bold' },
                                            bodyFont: { size: 13 },
                                            borderColor: 'rgba(255, 255, 255, 0.1)',
                                            borderWidth: 1,
                                            cornerRadius: 8,
                                            callbacks: {
                                                title: function(context) {
                                                    return products[context[0].dataIndex].name || 'Unknown Product';
                                                },
                                                label: function(context) {
                                                    const product = products[context.dataIndex];
                                                    if(viewType === 'revenue') {
                                                        return [
                                                            'Revenue: ₹' + parseFloat(product.revenue || 0).toFixed(2),
                                                            'Quantity Sold: ' + product.quantity,
                                                            'Avg Price: ₹' + (product.quantity > 0 ? (product.revenue / product.quantity).toFixed(2) : '0.00')
                                                        ];
                                                    } else {
                                                        return [
                                                            'Quantity: ' + product.quantity,
                                                            'Revenue: ₹' + parseFloat(product.revenue || 0).toFixed(2),
                                                            'Avg Price: ₹' + (product.quantity > 0 ? (product.revenue / product.quantity).toFixed(2) : '0.00')
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            beginAtZero: true,
                                            ticks: {
                                                font: { size: 11, weight: '500' },
                                                color: '#6b7280',
                                                callback: viewType === 'revenue' 
                                                    ? function(value) {
                                                        if(value >= 1000) return '₹' + (value / 1000).toFixed(1) + 'k';
                                                        return '₹' + value.toFixed(0);
                                                    }
                                                    : function(value) { return value; }
                                            },
                                            grid: {
                                                color: 'rgba(0, 0, 0, 0.05)',
                                                drawBorder: false,
                                            }
                                        },
                                        y: {
                                            categoryPercentage: 0.6, // Controls spacing between categories (bars) - reduced for tighter spacing
                                            barPercentage: 0.5, // Controls bar width within category - reduced for thinner bars
                                            ticks: {
                                                font: { size: 11, weight: '500' },
                                                color: '#6b7280'
                                            },
                                            grid: {
                                                display: false,
                                            }
                                        }
                                    }
                                }
                            });
                        }
                        
                        // Populate product details list
                        populateTopProductsList(products);
                    } else {
                        // No data
                        if(topProductsChart) {
                            topProductsChart.destroy();
                            topProductsChart = null;
                        }
                        $('#topProductsList').hide();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading top products chart:', xhr);
                $('#notEnoughDataMessage').show();
                $('#topProductsChartContainer').hide();
                $('#topProductsSummary').hide();
            }
        });
    }

    /**
     * Load Enhanced Recent Orders
     */
    function loadRecentOrders() {
        const limit = $('#recentOrdersLimit').val() || 5;
        
        $.ajax({
            url: '{{ route("dashboard.orders") }}',
            type: 'GET',
            data: { limit: limit },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    let tbody = $('#ordersTableBody');
                    tbody.empty();
                    
                    if(response.data && response.data.length > 0) {
                        response.data.forEach(function(order) {
                            const status = order.status || 'pending';
                            const paymentStatus = order.payment_status || 'pending';
                            
                            const statusClass = status === 'completed' || status === 'delivered' ? 'bg-success' :
                                                status === 'cancelled' || status === 'refunded' ? 'bg-danger' :
                                                status === 'pending' ? 'bg-warning' :
                                                status === 'processing' || status === 'confirmed' ? 'bg-info' :
                                                'bg-secondary';
                            
                            const paymentStatusClass = paymentStatus === 'paid' ? 'bg-success' :
                                                       paymentStatus === 'pending' ? 'bg-warning' :
                                                       paymentStatus === 'failed' ? 'bg-danger' :
                                                       'bg-secondary';
                            
                            const formattedDate = order.date_formatted || new Date(order.date).toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            });
                            
                            const orderNumberShort = order.order_number ? 
                                (order.order_number.length > 12 ? order.order_number.substring(0, 12) + '...' : order.order_number) 
                                : 'N/A';
                            
                            let row = `
                                <tr class="order-row" data-order-id="${order.id}">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <a href="{{ url('/orders') }}?id=${order.id}" class="text-reset fw-medium text-decoration-none" title="${order.order_number || 'N/A'}">
                                                ${orderNumberShort}
                                            </a>
                                            <small class="text-muted">${order.item_count || 0} item${order.item_count !== 1 ? 's' : ''}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">${order.customer_name || 'Guest'}</span>
                                            ${order.customer_id ? `<small class="text-muted">ID: ${order.customer_id}</small>` : ''}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-primary">₹${parseFloat(order.total || 0).toFixed(2)}</span>
                                            <small class="text-muted">
                                                <span class="badge ${paymentStatusClass} badge-sm">${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}</span>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="small">${formattedDate}</span>
                                            ${order.time ? `<small class="text-muted">${order.time}</small>` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });
                    } else {
                        tbody.html('<tr><td colspan="4" class="text-center py-4 text-muted">No orders found</td></tr>');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading orders:', xhr);
                $('#ordersTableBody').html('<tr><td colspan="4" class="text-center py-4 text-danger">Error loading orders</td></tr>');
            }
        });
    }

    // Event Handlers
    $('#dateFilter').on('change', function() {
        const period = $(this).val();
        loadDashboardStats(period);
        loadSalesChart(period);
    });
    
    // Period Toggle Handler for Sales Chart
    $('input[name="periodToggle"]').on('change', function() {
        const period = $(this).val();
        loadSalesChart(period);
    });

    // Top Products Event Handlers
    $('#topProductsLimit').on('change', function() {
        loadTopProductsChart();
    });
    
    $('input[name="topProductsView"]').on('change', function() {
        loadTopProductsChart();
    });
    
    $('#refreshTopProducts').on('click', function(e) {
        e.preventDefault();
        const icon = $(this).find('i');
        icon.addClass('bx-spin');
        loadTopProductsChart();
        setTimeout(() => icon.removeClass('bx-spin'), 1000);
    });
    
    // Recent Orders Event Handlers
    $('#recentOrdersLimit').on('change', function() {
        loadRecentOrders();
    });

    $('#refreshOrders').on('click', function(e) {
        e.preventDefault();
        const icon = $(this).find('i');
        icon.addClass('bx-spin');
        loadRecentOrders();
        setTimeout(() => icon.removeClass('bx-spin'), 1000);
    });

    $('#refreshSalesChart').on('click', function(e) {
        e.preventDefault();
        const icon = $(this).find('i');
        icon.addClass('bx-spin');
        const salesPeriod = $('input[name="periodToggle"]:checked').val() || '30day';
        loadSalesChart(salesPeriod);
        setTimeout(() => icon.removeClass('bx-spin'), 1000);
    });

    $('#refreshTopProducts').on('click', function(e) {
        e.preventDefault();
        const icon = $(this).find('i');
        icon.addClass('bx-spin');
        loadTopProductsChart();
        setTimeout(() => icon.removeClass('bx-spin'), 1000);
    });

    // Initial Load
    loadDashboardStats('week');
    loadSalesChart('30day'); // Default to 30-day
    loadOrdersByStatusChart();
    loadTopProductsChart();
    loadRecentOrders();
    
    // Set default view type for top products
    $('#viewQuantity').prop('checked', true);

    // Auto-refresh every 5 minutes
    setInterval(function() {
        const period = $('#dateFilter').val();
        loadDashboardStats(period);
        const salesPeriod = $('input[name="periodToggle"]:checked').val() || '30day';
        loadSalesChart(salesPeriod);
        loadRecentOrders();
    }, 300000); // 5 minutes
});
</script>
@endpush
