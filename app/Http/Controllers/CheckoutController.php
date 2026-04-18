<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Show the checkout page with server-verified cart data.
     */
    public function show()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Your cart is empty.');
        }

        // Generate a unique checkout token to prevent duplicate submissions
        $checkoutToken = Str::uuid()->toString();
        session(['checkout_token' => $checkoutToken]);

        // Determine if cart has any stitched items (forces card-only payment)
        $hasStitched = false;
        foreach ($cart as $item) {
            if (isset($item['type']) && strtolower(trim($item['type'])) === 'stitched') {
                $hasStitched = true;
                break;
            }
        }

        return view('checkout', [
            'checkoutToken' => $checkoutToken,
            'hasStitched'   => $hasStitched,
            'stripeKey'     => config('services.stripe.key'),
        ]);
    }

    /**
     * Process the checkout form submission.
     * Handles both COD and Stripe payment methods.
     */
    public function process(Request $request, NotificationService $notificationService)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('home')->with('error', 'Your cart is empty.');
        }

        // ── Input validation ──
        $validated = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'phone'           => 'required|string|max:20',
            'email'           => 'required|email|max:255',
            'address'         => 'required|string|max:500',
            'apartment'       => 'nullable|string|max:255',
            'city'            => 'required|string|max:100',
            'postal_code'     => 'nullable|string|max:20',
            'payment_method'  => 'required|in:cod,stripe',
            'checkout_token'  => 'required|string|max:100',
            'payment_intent_id' => 'nullable|string|max:255',
        ]);

        // ── Duplicate submission prevention ──
        $sessionToken = session('checkout_token');
        if (!$sessionToken || $sessionToken !== $validated['checkout_token']) {
            return redirect()->route('checkout')
                ->with('error', 'This checkout has already been submitted. Please start again.');
        }

        if (Order::where('checkout_token', $validated['checkout_token'])->exists()) {
            session()->forget('checkout_token');
            return redirect()->route('home')
                ->with('info', 'Your order has already been placed.');
        }

        // ── Server-side price verification ──
        $validatedCart = [];
        $hasStitched = false;

        foreach ($cart as $key => $item) {
            $product = Product::find($item['id']);

            if (!$product) {
                return redirect()->route('home')
                    ->with('error', 'A product in your cart is no longer available.');
            }

            $actualPrice = $product->getEffectivePrice($item['type'] ?? null);

            if ($product->stock_quantity !== null && $product->stock_quantity < $item['quantity']) {
                return redirect()->route('checkout')
                    ->with('error', "Insufficient stock for \"{$product->name}\". Only {$product->stock_quantity} available.");
            }

            if ($item['quantity'] < 1 || $item['quantity'] > 50) {
                return redirect()->route('checkout')->with('error', 'Invalid quantity detected.');
            }

            // Check if item type is stitched
            if (isset($item['type']) && strtolower(trim($item['type'])) === 'stitched') {
                $hasStitched = true;
            }

            $validatedCart[$key] = $item;
            $validatedCart[$key]['price'] = $actualPrice;
            $validatedCart[$key]['product_name'] = $product->name;
        }

        // ── BACKEND ENFORCEMENT: Stitched items require card payment ──
        if ($hasStitched && $validated['payment_method'] === 'cod') {
            return redirect()->route('checkout')
                ->with('error', 'Stitched items require advance payment by credit card. Cash on Delivery is not available.');
        }

        // ── Calculate totals ──
        $itemTotal = array_reduce($validatedCart, fn($carry, $item) => $carry + ($item['price'] * $item['quantity']), 0);
        $shippingCharge = ($validated['payment_method'] === 'cod') ? 250 : 200;
        $totalPrice = $itemTotal + $shippingCharge;

        $fullAddress = $validated['address'];
        if (!empty($validated['apartment'])) {
            $fullAddress .= ', ' . $validated['apartment'];
        }
        if (!empty($validated['postal_code'])) {
            $fullAddress .= ', Postal Code: ' . $validated['postal_code'];
        }

        // ── Stripe card payment: verify payment before creating order ──
        if ($validated['payment_method'] === 'stripe') {
            $paymentIntentId = $validated['payment_intent_id'] ?? null;

            if (!$paymentIntentId) {
                return redirect()->route('checkout')
                    ->with('error', 'Payment information is missing. Please try again.');
            }

            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

                // Verify payment succeeded
                if ($paymentIntent->status !== 'succeeded') {
                    return redirect()->route('checkout')
                        ->with('error', 'Payment was not successful. Please try again.');
                }

                // Verify payment amount matches order total (prevent manipulation)
                $expectedAmount = (int) ($totalPrice * 100);
                if ($paymentIntent->amount !== $expectedAmount) {
                    Log::warning("Payment amount mismatch: expected {$expectedAmount}, got {$paymentIntent->amount}");
                    return redirect()->route('checkout')
                        ->with('error', 'Payment amount verification failed. Please try again.');
                }

            } catch (\Exception $e) {
                Log::error('Stripe payment verification failed: ' . $e->getMessage());
                return redirect()->route('checkout')
                    ->with('error', 'Unable to verify payment. Please try again.');
            }
        }

        // ── Create order in DB transaction (only after payment is verified) ──
        $order = DB::transaction(function () use ($validated, $validatedCart, $totalPrice, $shippingCharge, $fullAddress) {
            $order = Order::create([
                'user_id'        => auth()->id(),
                'customer_name'  => $validated['first_name'] . ' ' . $validated['last_name'],
                'phone'          => $validated['phone'],
                'email'          => $validated['email'],
                'address'        => $fullAddress,
                'city'           => $validated['city'],
                'total_price'    => $totalPrice,
                'shipping_charge'=> $shippingCharge,
                'status'         => Order::STATUS_PENDING,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'stripe'
                    ? Order::PAYMENT_STATUS_PAID
                    : Order::PAYMENT_STATUS_PENDING,
                'checkout_token' => $validated['checkout_token'],
                'transaction_id' => $validated['payment_intent_id'] ?? null,
            ]);

            foreach ($validatedCart as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'size'       => $item['size'],
                    'type'       => $item['type'] ?? null,
                    'color'      => $item['color'] ?? null,
                ]);
            }

            return $order;
        });

        // Consume the checkout token
        session()->forget('checkout_token');

        // Send notification and clear cart
        $notificationService->notifyOrderPlaced($order);
        session()->forget('cart');

        return redirect()->route('order.success', ['order' => $order->id]);
    }

    /**
     * Create a Stripe PaymentIntent for the checkout total.
     * Called via AJAX from the checkout page.
     */
    public function createPaymentIntent(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Server-side price calculation (never trust frontend amounts)
        $itemTotal = 0;
        foreach ($cart as $item) {
            $product = Product::find($item['id']);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 400);
            }
            $actualPrice = $product->getEffectivePrice($item['type'] ?? null);
            $itemTotal += $actualPrice * $item['quantity'];
        }

        $shippingCharge = 200; // Card payment shipping
        $totalPrice = $itemTotal + $shippingCharge;
        $amountInPaisa = (int) ($totalPrice * 100);

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $paymentIntent = $stripe->paymentIntents->create([
                'amount'   => $amountInPaisa,
                'currency' => 'pkr',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'cart_total' => $totalPrice,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentIntent creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to initialize payment'], 500);
        }
    }

    /**
     * Handle Stripe webhook events.
     * Source of truth for payment confirmation.
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: Invalid payload');
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: Invalid signature');
            return response('Invalid signature', 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;

            $order = Order::where('transaction_id', $paymentIntent->id)->first();

            if ($order && $order->payment_status !== Order::PAYMENT_STATUS_PAID) {
                $order->update([
                    'payment_status' => Order::PAYMENT_STATUS_PAID,
                ]);

                Log::info("Stripe webhook: Order #{$order->id} confirmed paid via webhook.");
            }
        }

        return response('OK', 200);
    }
}
