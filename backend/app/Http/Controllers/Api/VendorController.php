<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VendorController extends Controller
{
    /**
     * Get vendor dashboard statistics
     */
    public function dashboard(Request $request)
    {
        $vendorId = auth()->id();

        // Check if user is a vendor
        if (!auth()->user()->hasVendorRole()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.'
            ], 403);
        }

        // Get statistics with proper error handling
        try {
            $stats = [
                'total_products' => Product::where('vendor_id', $vendorId)->count(),
                'total_orders' => OrderItem::whereHas('product', function($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                })->distinct('order_id')->count(),
                'total_revenue' => OrderItem::whereHas('product', function($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                })->whereHas('order', function($query) {
                    $query->where('status', '!=', 'cancelled')
                          ->where('status', '!=', 'refunded');
                })->sum(DB::raw('price * quantity')),
                'pending_orders' => OrderItem::whereHas('product', function($query) use ($vendorId) {
                    $query->where('vendor_id', $vendorId);
                })->whereHas('order', function($query) {
                    $query->whereIn('status', ['pending', 'processing']);
                })->distinct('order_id')->count(),
                'low_stock_products' => Product::where('vendor_id', $vendorId)
                    ->where('stock_quantity', '<=', 5)
                    ->where('is_active', true)
                    ->count(),
            ];

            // Get recent orders
            $recentOrders = Order::whereHas('items.product', function($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->with(['user', 'items' => function($query) use ($vendorId) {
                $query->whereHas('product', function($q) use ($vendorId) {
                    $q->where('vendor_id', $vendorId);
                })->with('product');
            }])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

            // Get top selling products
            $topProducts = Product::where('vendor_id', $vendorId)
                ->withCount(['orderItems as total_sold' => function($query) {
                    $query->whereHas('order', function($q) {
                        $q->where('status', '!=', 'cancelled')
                          ->where('status', '!=', 'refunded');
                    });
                }])
                ->where('is_active', true)
                ->orderBy('total_sold', 'desc')
                ->limit(5)
                ->get();

            // Get monthly revenue data for chart
            $monthlyRevenue = OrderItem::whereHas('product', function($query) use ($vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function($query) {
                $query->where('status', '!=', 'cancelled')
                      ->where('status', '!=', 'refunded');
            })
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(price * quantity) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->map(function($item) {
                $monthName = date('F', mktime(0, 0, 0, $item->month, 1));
                $item->month_name = $monthName;
                $item->label = $monthName . ' ' . $item->year;
                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_orders' => $recentOrders,
                    'top_products' => $topProducts,
                    'monthly_revenue' => $monthlyRevenue,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor dashboard error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get vendor orders with filtering
     */
    public function orders(Request $request)
    {
        try {
            $vendorId = auth()->id();
            $perPage = $request->per_page ?? 15;
            $status = $request->status;
            $search = $request->search;
            $sort = $request->sort ?? 'newest';

            $query = Order::whereHas('items.product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });

            if ($status) {
                $query->where('status', $status);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'LIKE', "%{$search}%")
                                    ->orWhere('email', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Sort
            switch ($sort) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'total_high':
                    $query->orderBy('total', 'desc');
                    break;
                case 'total_low':
                    $query->orderBy('total', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            // ✅ FIX: Removed 'shippingAddress' from with()
            $orders = $query->with([
                'user',
                'items' => function($q) use ($vendorId) {
                    $q->whereHas('product', function($query) use ($vendorId) {
                        $query->where('vendor_id', $vendorId);
                    })->with('product.images');
                }
            ])->paginate($perPage);

            // Calculate vendor-specific totals for each order
            $orders->getCollection()->transform(function($order) {
                $vendorItems = $order->items->filter(function($item) {
                    return $item->product && $item->product->vendor_id === auth()->id();
                });
                
                $order->vendor_subtotal = $vendorItems->sum(function($item) {
                    return $item->price * $item->quantity;
                });
                
                $order->vendor_items_count = $vendorItems->count();
                
                return $order;
            });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor orders error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load orders: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get single order details for vendor
     */
    public function orderDetails(Request $request, $id)
    {
        try {
            $vendorId = auth()->id();

            // ✅ FIX: Removed 'shippingAddress' from with()
            $order = Order::with([
                'user',
                'items' => function($q) use ($vendorId) {
                    $q->whereHas('product', function($query) use ($vendorId) {
                        $query->where('vendor_id', $vendorId);
                    })->with('product.images');
                }
            ])->whereHas('items.product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Calculate vendor totals
            $vendorItems = $order->items->filter(function($item) use ($vendorId) {
                return $item->product && $item->product->vendor_id === $vendorId;
            });
            
            $order->vendor_subtotal = $vendorItems->sum(function($item) {
                return $item->price * $item->quantity;
            });
            
            $order->vendor_items_count = $vendorItems->count();

            return response()->json([
                'success' => true,
                'data' => $order
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor order details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order details'
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        try {
            $vendorId = auth()->id();

            $validated = $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded'
            ]);

            // Check if order belongs to this vendor
            $order = Order::whereHas('items.product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })->find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or does not belong to you'
                ], 404);
            }

            // Update only the status
            $order->update(['status' => $validated['status']]);

            // Log the status change
            Log::info('Order status updated', [
                'order_id' => $orderId,
                'vendor_id' => $vendorId,
                'new_status' => $validated['status']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order->load(['user', 'items.product'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Order status update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get order analytics for vendor
     */
    public function orderAnalytics(Request $request)
    {
        try {
            $vendorId = auth()->id();
            $period = $request->period ?? 'month';

            $startDate = now();
            switch ($period) {
                case 'week':
                    $startDate = now()->subWeek();
                    break;
                case 'month':
                    $startDate = now()->subMonth();
                    break;
                case 'year':
                    $startDate = now()->subYear();
                    break;
                default:
                    $startDate = now()->subMonth();
            }

            // Get orders for vendor
            $orders = Order::whereHas('items.product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->where('created_at', '>=', $startDate)
            ->get();

            // Calculate analytics
            $totalOrders = $orders->count();
            $totalRevenue = $orders->sum('total');
            $totalItems = OrderItem::whereHas('product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate);
            })
            ->sum('quantity');

            // Status breakdown
            $statusBreakdown = [
                'pending' => 0,
                'processing' => 0,
                'shipped' => 0,
                'delivered' => 0,
                'cancelled' => 0
            ];

            foreach ($orders as $order) {
                if (isset($statusBreakdown[$order->status])) {
                    $statusBreakdown[$order->status]++;
                }
            }

            // Daily sales data for chart
            $dailySales = Order::whereHas('items.product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

            // Average order value
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            // Recent orders (last 5)
            $recentOrders = Order::whereHas('items.product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => now()->format('Y-m-d'),
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'total_items_sold' => $totalItems,
                    'average_order_value' => $avgOrderValue,
                    'status_breakdown' => $statusBreakdown,
                    'daily_sales' => $dailySales,
                    'recent_orders' => $recentOrders,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Order analytics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order analytics'
            ], 500);
        }
    }

    /**
     * Get vendor sales report
     */
    public function salesReport(Request $request)
    {
        try {
            $vendorId = auth()->id();
            $period = $request->period ?? 'month';
            $format = $request->format ?? 'json';

            $startDate = now();
            switch ($period) {
                case 'day':
                    $startDate = now()->subDay();
                    break;
                case 'week':
                    $startDate = now()->subWeek();
                    break;
                case 'month':
                    $startDate = now()->subMonth();
                    break;
                case 'year':
                    $startDate = now()->subYear();
                    break;
                default:
                    $startDate = now()->subMonth();
            }

            // Sales by product
            $productSales = OrderItem::whereHas('product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate)
                  ->where('status', '!=', 'cancelled');
            })
            ->with('product')
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('product_id')
            ->orderBy('total_revenue', 'desc')
            ->get();

            // Sales by category
            $categorySales = OrderItem::whereHas('product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function($q) use ($startDate) {
                $q->where('created_at', '>=', $startDate)
                  ->where('status', '!=', 'cancelled');
            })
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

            // Monthly sales trend
            $monthlyTrend = Order::whereHas('items.product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->where('created_at', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

            $reportData = [
                'period' => $period,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'product_sales' => $productSales,
                'category_sales' => $categorySales,
                'monthly_trend' => $monthlyTrend,
                'summary' => [
                    'total_products_sold' => $productSales->sum('total_quantity'),
                    'total_revenue' => $productSales->sum('total_revenue'),
                    'total_categories' => $categorySales->count(),
                    'top_product' => $productSales->first()?->product?->name ?? 'N/A',
                ]
            ];

            // If CSV format is requested
            if ($format === 'csv') {
                return $this->exportSalesCSV($reportData);
            }

            return response()->json([
                'success' => true,
                'data' => $reportData
            ]);

        } catch (\Exception $e) {
            Log::error('Sales report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate sales report'
            ], 500);
        }
    }

    /**
     * Export sales report as CSV
     */
    private function exportSalesCSV($data)
    {
        $filename = "sales_report_{$data['period']}_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($handle, ['Sales Report - ShopSphere']);
            fputcsv($handle, ['Period', $data['period']]);
            fputcsv($handle, ['Start Date', $data['start_date']]);
            fputcsv($handle, ['End Date', $data['end_date']]);
            fputcsv($handle, []);
            
            // Summary
            fputcsv($handle, ['SUMMARY']);
            fputcsv($handle, ['Total Products Sold', $data['summary']['total_products_sold']]);
            fputcsv($handle, ['Total Revenue', '$' . number_format($data['summary']['total_revenue'], 2)]);
            fputcsv($handle, ['Top Product', $data['summary']['top_product']]);
            fputcsv($handle, []);
            
            // Product sales
            fputcsv($handle, ['PRODUCT SALES']);
            fputcsv($handle, ['Product', 'Quantity Sold', 'Revenue']);
            foreach ($data['product_sales'] as $item) {
                fputcsv($handle, [
                    $item->product->name ?? 'Unknown',
                    $item->total_quantity,
                    '$' . number_format($item->total_revenue, 2)
                ]);
            }
            
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get vendor profile
     */
    public function profile(Request $request)
    {
        try {
            $user = auth()->user()->load('vendor');
            
            // Add vendor stats to profile
            if ($user->vendor) {
                $user->vendor->stats = [
                    'total_products' => Product::where('vendor_id', $user->id)->count(),
                    'total_orders' => OrderItem::whereHas('product', function($q) use ($user) {
                        $q->where('vendor_id', $user->id);
                    })->distinct('order_id')->count(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor profile error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load profile'
            ], 500);
        }
    }

    /**
     * Update vendor profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'store_name' => 'sometimes|string|max:255',
                'store_description' => 'sometimes|string|max:1000',
                'store_address' => 'sometimes|string|max:500',
                'store_phone' => 'sometimes|string|max:20',
            ]);

            // Update user
            $userData = [];
            if ($request->has('name')) {
                $userData['name'] = $validated['name'];
            }
            if ($request->has('phone')) {
                $userData['phone'] = $validated['phone'];
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                $path = $request->file('avatar')->store('avatars/vendors', 'public');
                $userData['avatar'] = $path;
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            // Update vendor profile
            if ($user->vendor) {
                $vendorData = [];
                if ($request->has('store_name')) {
                    $vendorData['store_name'] = $validated['store_name'];
                }
                if ($request->has('store_description')) {
                    $vendorData['store_description'] = $validated['store_description'];
                }
                if ($request->has('store_address')) {
                    $vendorData['store_address'] = $validated['store_address'];
                }
                if ($request->has('store_phone')) {
                    $vendorData['store_phone'] = $validated['store_phone'];
                }

                if (!empty($vendorData)) {
                    $user->vendor->update($vendorData);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()->load('vendor')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Vendor profile update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get vendor products with low stock
     */
    public function lowStockProducts(Request $request)
    {
        try {
            $vendorId = auth()->id();
            $threshold = $request->threshold ?? 5;

            $products = Product::where('vendor_id', $vendorId)
                ->where('stock_quantity', '<=', $threshold)
                ->where('is_active', true)
                ->orderBy('stock_quantity', 'asc')
                ->with('category')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Low stock products error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load low stock products'
            ], 500);
        }
    }

    /**
     * Get vendor statistics overview
     */
    public function stats(Request $request)
    {
        try {
            $vendorId = auth()->id();

            // Get today's stats
            $today = now()->startOfDay();
            $todayRevenue = OrderItem::whereHas('product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->where('created_at', '>=', $today)
            ->sum(DB::raw('price * quantity'));

            $todayOrders = OrderItem::whereHas('product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->where('created_at', '>=', $today)
            ->distinct('order_id')
            ->count();

            // Get this month's stats
            $monthStart = now()->startOfMonth();
            $monthRevenue = OrderItem::whereHas('product', function($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->whereHas('order', function($q) {
                $q->where('status', '!=', 'cancelled');
            })
            ->where('created_at', '>=', $monthStart)
            ->sum(DB::raw('price * quantity'));

            return response()->json([
                'success' => true,
                'data' => [
                    'today' => [
                        'revenue' => $todayRevenue,
                        'orders' => $todayOrders,
                    ],
                    'month' => [
                        'revenue' => $monthRevenue,
                    ],
                    'overall' => [
                        'total_products' => Product::where('vendor_id', $vendorId)->count(),
                        'total_orders' => OrderItem::whereHas('product', function($q) use ($vendorId) {
                            $q->where('vendor_id', $vendorId);
                        })->distinct('order_id')->count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Vendor stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }
}