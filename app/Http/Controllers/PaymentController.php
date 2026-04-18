<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Services\AdminLogger;

class PaymentController extends Controller
{
    /**
     * Show payment processing page or handle automatic redirection
     */
    public function processPayment(Order $order)
    {
        if ($order->payment_status === 'paid') {
            return redirect()->route('home')->with('info', 'This order is already paid.');
        }

        // Verify order is still pending
        if ($order->status !== Order::STATUS_PENDING) {
            return redirect()->route('home')->with('error', 'This order cannot be processed.');
        }

        return view('payment-process', compact('order'));
    }

    /**
     * Simulate Payment Success (For JazzCash/EasyPaisa/Stripe Test)
     * In production, this would be a webhook endpoint with signature verification.
     */
    public function simulateSuccess(Request $request, Order $order, NotificationService $notificationService)
    {
        // Prevent double-processing
        if ($order->payment_status === 'paid') {
            return redirect()->route('order.success', ['order' => $order->id])
                ->with('info', 'This order is already paid.');
        }

        // Verify order is in valid state for payment
        if ($order->status !== Order::STATUS_PENDING) {
            return redirect()->route('home')->with('error', 'This order cannot be processed.');
        }

        // Use DB transaction for atomic payment update
        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'paid',
                'transaction_id' => 'TRX-' . strtoupper(bin2hex(random_bytes(8))),
            ]);
        });

        // Clear cart now that payment is successful
        session()->forget('cart');

        // Send notification
        $notificationService->notifyOrderPlaced($order);

        return redirect()->route('order.success', ['order' => $order->id]);
    }

    /**
     * Simulate Payment Failure
     * Marks order payment as failed and redirects user back.
     */
    public function simulateFailure(Order $order)
    {
        // Mark payment as failed instead of leaving in limbo
        if ($order->payment_status === 'pending') {
            $order->update(['payment_status' => 'failed']);
        }

        return redirect()->route('checkout')->with('error', 'Payment failed. Please try again or select another method.');
    }
}
