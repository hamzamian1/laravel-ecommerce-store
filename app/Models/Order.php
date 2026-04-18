<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Payment constants
    const PAYMENT_COD = 'cod';
    const PAYMENT_STRIPE = 'stripe';
    const PAYMENT_JAZZCASH = 'jazzcash';
    const PAYMENT_EASYPAISA = 'easypaisa';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';

    /**
     * Valid status transitions (state machine).
     * Key = current status, Value = array of allowed next statuses.
     */
    const STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED, self::STATUS_CANCELLED],
        self::STATUS_DELIVERED => [self::STATUS_COMPLETED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected $fillable = [
        'user_id',
        'customer_name', 
        'phone', 
        'email',
        'address', 
        'city',
        'total_price', 
        'shipping_charge',
        'status',
        'payment_method',
        'payment_status',
        'transaction_id',
        'order_number',
        'checkout_token',
        'stripe_session_id',
    ];

    /**
     * Boot method to auto-generate secure order number on creation.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate a unique, non-sequential order number.
     * Format: OXY-YYYY-XXXXXX (e.g. OXY-2026-A3F9B2)
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'OXY-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Check if a status transition is valid.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = self::STATUS_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Get status color for UI display
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_CONFIRMED => 'info',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_SHIPPED => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_COMPLETED => 'secondary',
            self::STATUS_CANCELLED => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Check if order can be confirmed by customer
     */
    public function canBeConfirmed()
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if order is completed
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if order is delivered
     */
    public function isDelivered()
    {
        return $this->status === self::STATUS_DELIVERED;
    }
}
