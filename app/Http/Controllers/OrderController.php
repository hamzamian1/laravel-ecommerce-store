<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\AdminLogger;

class OrderController extends Controller
{
    /**
     * Update order status (Admin only)
     * Enforces strict status transition rules and logs admin activity.
     */
    public function updateStatus(Request $request, Order $order, \App\Services\NotificationService $notificationService)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Order::getStatuses()),
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // Enforce strict state machine transitions
        if (!$order->canTransitionTo($newStatus)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot change status from '{$oldStatus}' to '{$newStatus}'.",
            ], 422);
        }

        $order->update(['status' => $newStatus]);

        // Decrement stock when shipped
        if ($newStatus === Order::STATUS_SHIPPED && $oldStatus !== Order::STATUS_SHIPPED) {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->decrement('stock_quantity', $item->quantity);
                }
            }
        }

        // Log admin action
        AdminLogger::log('update_order_status', 'Order', $order->id, [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        // Send status update notification
        $notificationService->notifyStatusUpdate($order, $newStatus);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'status' => $order->status,
            'status_color' => $order->status_color,
        ]);
    }

    /**
     * Track order (Authenticated users only)
     * Users can only track their own orders.
     */
    public function trackOrder(Request $request)
    {
        $userOrders = Order::where('user_id', auth()->id())
            ->latest()
            ->get(['id', 'order_number', 'status', 'total_price', 'created_at', 'payment_method']);

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'order_id' => 'required|string|max:20',
            ]);

            $order = Order::with('items.product')
                ->where('order_number', $validated['order_id'])
                ->where('user_id', auth()->id())
                ->first();

            if (!$order) {
                return back()->withErrors(['error' => 'Order not found. Please check your Order ID.']);
            }

            return view('track-order', compact('order', 'userOrders'));
        }

        return view('track-order', ['order' => null, 'userOrders' => $userOrders]);
    }

    /**
     * Order history — list all orders for the logged-in user.
     */
    public function myOrders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return view('my-orders', compact('orders'));
    }

    /**
     * Confirm delivery (Customer action — authenticated)
     */
    public function confirmDelivery(Request $request, Order $order, \App\Services\NotificationService $notificationService)
    {
        // Verify the authenticated user owns this order
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Check if order can be confirmed
        if (!$order->canBeConfirmed()) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be confirmed at this time. Current status: ' . ucfirst($order->status),
            ], 400);
        }

        $order->update(['status' => Order::STATUS_COMPLETED]);

        // Notify about completion
        $notificationService->notifyStatusUpdate($order, Order::STATUS_COMPLETED);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your delivery has been confirmed.',
            'status' => $order->status,
        ]);
    }

    /**
     * Show order success/receipt page
     */
    public function success(Order $order)
    {
        return view('success', compact('order'));
    }

    /**
     * Simulate payment processing for non-COD methods
     */
    public function processPayment(Order $order, \App\Services\NotificationService $notificationService)
    {
        // Prevent re-processing of already paid orders
        if ($order->payment_status === 'paid') {
            return redirect()->route('home')->with('info', 'This order is already paid.');
        }

        // Verify the order is still in pending status
        if ($order->status !== Order::STATUS_PENDING) {
            return redirect()->route('home')->with('error', 'This order cannot be processed.');
        }

        // Simulation: Mark as paid
        $order->update(['payment_status' => 'paid']);
        
        // Notify about successful order
        $notificationService->notifyOrderPlaced($order);
        
        // Clear cart
        session()->forget('cart');
        
        return redirect()->route('order.success', ['order' => $order->id])
            ->with('success', 'Payment successful! Your order has been placed.');
    }

    /**
     * Get count of pending orders for admin dashboard
     */
    public function getPendingCount()
    {
        $count = Order::where('status', 'pending')->count();
        return response()->json(['count' => $count]);
    }
}
