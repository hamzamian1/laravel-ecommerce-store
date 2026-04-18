<?php

namespace App\Services;

use App\Models\Order;
use App\Mail\OrderPlaced;
use App\Mail\OrderStatusUpdated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notify customer about new order
     */
    public function notifyOrderPlaced(Order $order)
    {
        // 1. Send Email to actual customer email
        try {
            if ($order->email) {
                Mail::to($order->email)
                    ->send(new OrderPlaced($order));
                Log::info("Order confirmation email sent for #{$order->order_number} to {$order->email}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send order email for #{$order->order_number}: " . $e->getMessage());
        }

        // 2. Send SMS (Mocked)
        $this->sendSMS($order->phone, "Shukriya! Aapka order {$order->order_number} confirm ho gaya hai. Aap yahan track kar sakte hain: " . route('order.track'));
    }

    /**
     * Notify customer about status update
     */
    public function notifyStatusUpdate(Order $order, $status)
    {
        // 1. Send Email to actual customer email
        try {
            if ($order->email) {
                Mail::to($order->email)
                    ->send(new OrderStatusUpdated($order, $status));
                Log::info("Status update email ({$status}) sent for #{$order->order_number} to {$order->email}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send status update email for #{$order->order_number}: " . $e->getMessage());
        }

        // 2. Send SMS (Mocked)
        $statusUrdu = $this->getStatusInUrdu($status);
        $this->sendSMS($order->phone, "Oxy-Bliss Update: Aapka order {$order->order_number} ab {$statusUrdu} stage par hai.");
    }

    /**
     * Mock SMS Sending
     */
    protected function sendSMS($phone, $message)
    {
        Log::channel('single')->info("--- SMS MOCK ---");
        Log::channel('single')->info("TO: {$phone}");
        Log::channel('single')->info("MESSAGE: {$message}");
        Log::channel('single')->info("----------------");
    }

    /**
     * Map status to Urdu for SMS
     */
    protected function getStatusInUrdu($status)
    {
        $map = [
            'pending' => 'intezar mein',
            'processing' => 'tayyar ho raha',
            'shipped' => 'bhej diya gaya',
            'delivered' => 'deliver ho gaya',
            'completed' => 'mukammal',
            'cancelled' => 'mansookh',
        ];

        return $map[strtolower($status)] ?? $status;
    }
}
