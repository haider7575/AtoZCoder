<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Products (Admin Only)
        $products = [];
        if ($user->isAdmin()) {
            $products = \App\Models\Product::orderBy('created_at', 'desc')->paginate(10, ['*'], 'products_page');
        }

        // Staff (Admin Only)
        $staffMembers = [];
        if ($user->isAdmin()) {
            $staffMembers = \App\Models\User::where('role', 'staff')->get();
        }

        // Orders
        $ordersQuery = \App\Models\Order::with([
            'items.product' => function ($query) {
                $query->withTrashed();
            },
            'user',
            'shipment',
            'staff'
        ])->orderBy('created_at', 'desc');

        if ($user->isStaff()) {
            $ordersQuery->where('assigned_staff_id', $user->id);
        }

        $orders = $ordersQuery->paginate(10, ['*'], 'orders_page');

        // For creating orders (need list of active products and staff)
        $activeProducts = \App\Models\Product::where('status', 'active')->get();
        $availableStaff = \App\Models\User::where('role', 'staff')->get();

        return view('dashboard', compact('products', 'orders', 'staffMembers', 'activeProducts', 'availableStaff'));
    }
}
