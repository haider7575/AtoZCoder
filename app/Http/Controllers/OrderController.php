<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Jobs\ProcessShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (Gate::allows('view-all-orders')) {
            return Order::with(['items.product', 'user', 'shipment'])->paginate(10);
        }

        if (Gate::allows('view-assigned-orders')) {
            return Order::with(['items.product', 'user', 'shipment'])
                ->where('assigned_staff_id', $user->id)
                ->paginate(10);
        }

        abort(403);
    }

    public function store(StoreOrderRequest $request)
    {
        // Validation and Authorization handled by StoreOrderRequest

        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $itemsToCreate = [];

            // Lock and check stock
            foreach ($validated['products'] as $itemData) {
                // LockForUpdate to prevent race conditions
                $product = Product::lockForUpdate()->find($itemData['id']);

                if ($product->stock_quantity < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $product->stock_quantity -= $itemData['quantity'];
                $product->save();

                $price = $product->price;
                $lineTotal = $price * $itemData['quantity'];
                $subtotal += $lineTotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $price,
                ];
            }

            $tax = $subtotal * 0.10;
            $total = $subtotal + $tax;

            $order = Order::create([
                'user_id' => $request->user()->id, // Assuming authenticated user is creating the order
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $total,
                'status' => 'pending',
                'assigned_staff_id' => $validated['assigned_staff_id'],
            ]);

            foreach ($itemsToCreate as $item) {
                $order->items()->create($item);
            }

            if ($request->wantsJson()) {
                return $order->load('items');
            }
            return redirect()->back()->with('success', 'Order Placed');
        });
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        // Validation and Authorization handled by UpdateOrderStatusRequest

        $validated = $request->validated();
        $newStatus = $validated['status'];
        $oldStatus = $order->status;

        // Validation of flow
        if ($oldStatus === 'cancelled') {
            if ($request->wantsJson()) return response()->json(['message' => 'Cannot change status of cancelled order'], 400);
            return redirect()->back()->with('error', 'Cannot change status of cancelled order');
        }

        if ($oldStatus === $newStatus) {
            if ($request->wantsJson()) return response()->json(['message' => 'Status is already ' . $newStatus], 200);
            return redirect()->back()->with('info', 'Status is already ' . $newStatus);
        }

        $transitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['shipped', 'cancelled'],
            'shipped' => ['cancelled'],
        ];

        if (!in_array($newStatus, $transitions[$oldStatus] ?? [])) {
            if ($request->wantsJson()) return response()->json(['message' => "Invalid status transition from $oldStatus to $newStatus"], 400);
            return redirect()->back()->with('error', "Invalid status transition from $oldStatus to $newStatus");
        }

        // Business Logic
        if ($newStatus === 'cancelled') {
            // Restore Stock
            foreach ($order->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }

        $order->status = $newStatus;
        $order->save();

        if ($newStatus === 'confirmed') {
            // "order confirmation should not wait for the API response"
            // "shipment creation should be handled via queued job"
            ProcessShipment::dispatch($order);
        }

        if ($request->wantsJson()) {
            return response()->json($order->load('shipment'));
        }
        return redirect()->back()->with('success', 'Order status updated');
    }
}
