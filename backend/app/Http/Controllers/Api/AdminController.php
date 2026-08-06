<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function dashboard(Request $request)
    {
        try {
            $totalUsers = User::count();
            $totalVendors = User::where('role', 'vendor')->count();
            $totalCustomers = User::where('role', 'customer')->count();
            $totalProducts = Product::count();
            $totalOrders = Order::count();
            $pendingOrders = Order::where('status', 'pending')->count();
            $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
            
            // Recent orders
            $recentOrders = Order::with(['user', 'items'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Pending vendors
            $pendingVendors = Vendor::where('is_approved', false)
                ->with('user')
                ->limit(5)
                ->get();
            
            // Monthly revenue for chart
            $monthlyRevenue = Order::where('status', '!=', 'cancelled')
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('SUM(total) as total')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get()
                ->map(function($item) {
                    $monthName = date('F', mktime(0, 0, 0, $item->month, 1));
                    $item->month_name = $monthName;
                    return $item;
                });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_users' => $totalUsers,
                        'total_vendors' => $totalVendors,
                        'total_customers' => $totalCustomers,
                        'total_products' => $totalProducts,
                        'total_orders' => $totalOrders,
                        'pending_orders' => $pendingOrders,
                        'total_revenue' => $totalRevenue,
                    ],
                    'recent_orders' => $recentOrders,
                    'pending_vendors' => $pendingVendors,
                    'monthly_revenue' => $monthlyRevenue,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Admin dashboard error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard'
            ], 500);
        }
    }

    /**
     * Get all users
     */
    public function users(Request $request)
    {
        try {
            $users = User::with('vendor')
                ->when($request->role, function($query, $role) {
                    return $query->where('role', $role);
                })
                ->when($request->search, function($query, $search) {
                    return $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
            
        } catch (\Exception $e) {
            Log::error('Users fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, $id)
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $request->validate([
                'role' => 'required|in:customer,vendor,admin'
            ]);
            
            $user->role = $request->role;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'User role updated successfully',
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            Log::error('Update user role error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user role'
            ], 500);
        }
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus(Request $request, $id)
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $user->is_active = !$user->is_active;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            Log::error('Toggle user status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status'
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }

    /**
     * Get all vendors (with pending filter)
     */
    public function vendors(Request $request)
    {
        try {
            $vendors = Vendor::with('user')
                ->when($request->status === 'pending', function($query) {
                    return $query->where('is_approved', false);
                })
                ->when($request->status === 'approved', function($query) {
                    return $query->where('is_approved', true);
                })
                ->when($request->search, function($query, $search) {
                    return $query->where('shop_name', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%");
                        });
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);
            
            return response()->json([
                'success' => true,
                'data' => $vendors
            ]);
            
        } catch (\Exception $e) {
            Log::error('Vendors fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch vendors'
            ], 500);
        }
    }

    /**
     * Approve vendor
     */
    public function approveVendor($id)
    {
        try {
            $vendor = Vendor::find($id);
            
            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found'
                ], 404);
            }
            
            $vendor->is_approved = true;
            $vendor->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Vendor approved successfully',
                'data' => $vendor
            ]);
            
        } catch (\Exception $e) {
            Log::error('Approve vendor error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve vendor'
            ], 500);
        }
    }

    /**
     * Suspend vendor
     */
    public function suspendVendor($id)
    {
        try {
            $vendor = Vendor::find($id);
            
            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found'
                ], 404);
            }
            
            $vendor->is_approved = false;
            $vendor->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Vendor suspended successfully',
                'data' => $vendor
            ]);
            
        } catch (\Exception $e) {
            Log::error('Suspend vendor error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend vendor'
            ], 500);
        }
    }

    /**
     * Get all orders
     */
    public function orders(Request $request)
    {
        try {
            $orders = Order::with(['user', 'items.product'])
                ->when($request->status, function($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->search, function($query, $search) {
                    return $query->where('order_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%");
                        });
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);
            
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
            
        } catch (\Exception $e) {
            Log::error('Orders fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders'
            ], 500);
        }
    }

    /**
     * Update order status (admin)
     */
    public function updateOrderStatus(Request $request, $id)
    {
        try {
            $order = Order::find($id);
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            
            $request->validate([
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
            ]);
            
            $order->status = $request->status;
            $order->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order
            ]);
            
        } catch (\Exception $e) {
            Log::error('Update order status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status'
            ], 500);
        }
    }

    /**
     * Get all categories
     */
    public function categories(Request $request)
    {
        try {
            $categories = Category::withCount('products')
                ->when($request->search, function($query, $search) {
                    return $query->where('name', 'LIKE', "%{$search}%");
                })
                ->orderBy('order', 'asc')
                ->paginate($request->per_page ?? 15);
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            Log::error('Categories fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories'
            ], 500);
        }
    }

    /**
     * Create category
     */
    public function createCategory(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories',
                'description' => 'nullable|string',
                'icon' => 'nullable|string|max:50',
            ]);
            
            $category = Category::create([
                'name' => $request->name,
                'slug' => \Str::slug($request->name),
                'description' => $request->description,
                'icon' => $request->icon,
                'order' => Category::count() + 1,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Create category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category'
            ], 500);
        }
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            $request->validate([
                'name' => 'sometimes|string|max:255|unique:categories,name,' . $id,
                'description' => 'nullable|string',
                'icon' => 'nullable|string|max:50',
                'order' => 'nullable|integer',
            ]);
            
            if ($request->has('name')) {
                $category->slug = \Str::slug($request->name);
            }
            
            $category->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);
            
        } catch (\Exception $e) {
            Log::error('Update category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category'
            ], 500);
        }
    }

    /**
     * Delete category
     */
    public function deleteCategory($id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete category error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category'
            ], 500);
        }
    }

    /**
     * Get system settings
     */
    public function settings()
    {
        // This would typically fetch from a settings table
        // For now, return default settings
        return response()->json([
            'success' => true,
            'data' => [
                'site_name' => 'ShopSphere',
                'site_logo' => null,
                'commission_rate' => 10,
                'currency' => 'USD',
                'tax_rate' => 8,
                'shipping_rate' => 10,
                'free_shipping_threshold' => 100,
            ]
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:3',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'shipping_rate' => 'nullable|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
        ]);
        
        // Would save to settings table
        // For now, just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $request->all()
        ]);
    }
}