<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout | Oxy-bliss</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #fff; color: #333; font-size: 14px; line-height: 1.5; }

        /* Layout */
        .checkout-wrap { display: flex; min-height: 100vh; }
        .checkout-left { flex: 1; max-width: 680px; margin: 0 auto; padding: 1rem 2rem 3rem 2rem; }
        .checkout-right { width: 420px; background: #fafafa; border-left: 1px solid #e5e5e5; padding: 2rem 2rem 3rem; position: sticky; top: 0; height: 100vh; overflow-y: auto; }

        /* Header */
        .checkout-logo { text-align: center; padding: 0.8rem 0; border-bottom: 1px solid #e5e5e5; margin-bottom: 1.2rem; }
        .checkout-logo a { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 400; letter-spacing: -1px; text-decoration: none; color: #000; }

        /* Section Titles */
        .section-title { font-size: 16px; font-weight: 700; color: #000; margin-bottom: 12px; }
        .section-subtitle { font-size: 12px; color: #707070; margin-bottom: 12px; }

        /* Input Fields - Jazmin Style */
        .field { margin-bottom: 12px; }
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .field-input {
            width: 100%; border: 1px solid #d9d9d9; border-radius: 5px;
            padding: 12px 14px; font-size: 13px; font-family: inherit; color: #333;
            background: #fff; outline: none; transition: border-color 0.2s;
        }
        .field-input:focus { border-color: #1a1a2e; box-shadow: 0 0 0 1px #1a1a2e; }
        .field-input.has-error { border-color: #e53935; }
        .field-input::placeholder { color: #999; }
        .field-error { color: #e53935; font-size: 11px; margin-top: 3px; }
        .field-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
        .field-label { font-size: 11px; color: #707070; margin-bottom: 4px; display: block; }
        .phone-wrap { position: relative; }
        .phone-prefix { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #333; pointer-events: none; display: flex; align-items: center; gap: 4px; padding-right: 10px; border-right: 1px solid #d9d9d9; }
        .phone-input { padding-left: 80px; }

        /* Checkbox & Radio */
        .check-row { display: flex; align-items: center; gap: 8px; margin: 8px 0; cursor: pointer; }
        .check-row input[type="checkbox"], .check-row input[type="radio"] { width: 16px; height: 16px; accent-color: #000; cursor: pointer; flex-shrink: 0; }
        .check-row label { font-size: 13px; color: #333; cursor: pointer; }

        /* Radio Group - Boxed Style (Jazmin) */
        .radio-group { border: 1px solid #d9d9d9; border-radius: 5px; overflow: hidden; }
        .radio-option { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #e5e5e5; }
        .radio-option:last-child { border-bottom: none; }
        .radio-option:hover { background: #fafafa; }
        .radio-option.selected { background: #f0f4ff; }
        .radio-option.disabled { opacity: 0.45; cursor: not-allowed; }
        .radio-option .radio-left { display: flex; align-items: center; gap: 10px; }
        .radio-option .radio-right { display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .radio-option input[type="radio"] { width: 18px; height: 18px; accent-color: #000; cursor: pointer; }
        .radio-option .option-label { font-size: 13px; font-weight: 500; color: #333; }
        .radio-option .option-sub { font-size: 11px; color: #888; }

        /* Card Icons */
        .card-icons { display: flex; gap: 4px; }
        .card-icon { height: 22px; }

        /* Stripe Elements */
        .stripe-fields { background: #f7f7f7; border-top: 1px solid #e5e5e5; padding: 16px; }
        .stripe-fields .StripeElement { border: 1px solid #d9d9d9; padding: 12px 14px; border-radius: 5px; background: #fff; transition: border-color 0.2s; font-size: 13px; }
        .stripe-fields .StripeElement--focus { border-color: #1a1a2e; box-shadow: 0 0 0 1px #1a1a2e; }
        .stripe-fields .StripeElement--invalid { border-color: #e53935; }
        .stripe-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px; }

        /* Stitched Notice */
        .stitched-notice { background: #fff8e6; border: 1px solid #f0d78c; color: #8a6d00; padding: 10px 14px; border-radius: 5px; font-size: 12px; margin-bottom: 12px; }

        /* Submit Button */
        .submit-btn {
            width: 100%; background: #1a1a2e; color: #fff; border: none;
            padding: 16px; font-size: 14px; font-weight: 600; border-radius: 5px;
            cursor: pointer; transition: background 0.2s; margin-top: 24px;
            font-family: inherit; letter-spacing: 0.5px;
        }
        .submit-btn:hover { background: #000; }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .submit-btn.loading { position: relative; pointer-events: none; }
        .submit-btn.loading::after {
            content: ''; position: absolute; width: 18px; height: 18px;
            border: 2px solid transparent; border-top-color: #fff;
            border-radius: 50%; animation: spin 0.6s linear infinite;
            right: 20px; top: 50%; transform: translateY(-50%);
        }
        @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

        /* Error */
        .payment-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 14px; border-radius: 5px; font-size: 12px; margin-top: 12px; }

        /* Order Summary (Right Side) */
        .summary-item { display: flex; gap: 14px; padding: 12px 0; align-items: center; }
        .summary-img { width: 64px; height: 64px; border-radius: 8px; border: 1px solid #e5e5e5; overflow: hidden; background: #f5f5f5; position: relative; flex-shrink: 0; }
        .summary-img img { width: 100%; height: 100%; object-fit: cover; }
        .summary-img .qty-badge { position: absolute; top: -6px; right: -6px; background: rgba(0,0,0,0.7); color: #fff; font-size: 10px; font-weight: 700; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .summary-name { font-size: 13px; font-weight: 600; color: #333; line-height: 1.3; }
        .summary-variant { font-size: 11px; color: #888; margin-top: 2px; }
        .summary-price { font-size: 13px; font-weight: 600; color: #333; margin-left: auto; white-space: nowrap; }
        .summary-divider { border: none; border-top: 1px solid #e5e5e5; margin: 16px 0; }
        .summary-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; color: #333; }
        .summary-row.total { font-size: 16px; font-weight: 700; padding-top: 12px; color: #000; }
        .summary-row .label { color: #707070; }
        .summary-row .pkr { font-size: 11px; color: #888; margin-right: 4px; }

        /* Fallback Image */
        .img-fallback { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f0f0f0; color: #aaa; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }

        /* Flash Messages */
        .flash-msg { padding: 10px 14px; border-radius: 5px; font-size: 12px; margin-bottom: 16px; }
        .flash-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .flash-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }

        /* Responsive */
        @media (max-width: 960px) {
            .checkout-wrap { flex-direction: column-reverse; }
            .checkout-right { width: 100%; border-left: none; border-bottom: 1px solid #e5e5e5; position: static; height: auto; }
            .checkout-left { max-width: 100%; margin: 0; padding: 1.5rem; }
            .checkout-logo a { font-size: 2.2rem !important; font-weight: 700 !important; }
        }

        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    @php
        $cart_items = session('cart', []);
        $item_total = 0;
        foreach ($cart_items as $ci) { $item_total += $ci['price'] * $ci['quantity']; }
    @endphp

    <div class="checkout-wrap" x-data="checkoutApp()" x-init="init()">

        <!-- LEFT COLUMN: Forms -->
        <div class="checkout-left">
            <!-- Logo -->
            <div class="checkout-logo">
                <a href="{{ route('home') }}">Oxy-Bliss</a>
            </div>

            {{-- Flash Messages --}}
            @if(session('error'))
                <div class="flash-msg flash-error">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="flash-msg flash-info">{{ session('info') }}</div>
            @endif

            <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form" @submit.prevent="handleSubmit">
                @csrf
                <input type="hidden" name="checkout_token" value="{{ $checkoutToken ?? '' }}">
                <input type="hidden" name="payment_intent_id" x-model="paymentIntentId">

                <!-- Contact -->
                <div style="margin-bottom: 28px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h2 class="section-title" style="margin: 0;">Contact</h2>
                        @guest
                            <a href="{{ route('login') }}" style="font-size: 12px; color: #1a73e8; text-decoration: underline;">Sign in</a>
                        @else
                            <a href="{{ route('account') }}" style="font-size: 12px; color: #1a73e8; text-decoration: underline;">My Account</a>
                        @endguest
                    </div>
                    <div class="field">
                        <input type="email" name="email" required placeholder="Email" value="{{ old('email', auth()->user()->email ?? '') }}"
                               class="field-input @error('email') has-error @enderror">
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="check-row">
                        <input type="checkbox" id="news_offers" name="news_offers" checked>
                        <label for="news_offers">Email me with news and offers</label>
                    </div>
                </div>

                <!-- Delivery -->
                <div style="margin-bottom: 28px;">
                    <h2 class="section-title">Delivery</h2>
                    <div class="field">
                        <label class="field-label">Country/Region</label>
                        <select name="country" class="field-input field-select">
                            <option value="Pakistan">Pakistan</option>
                        </select>
                    </div>
                    <div class="field-row">
                        <div>
                            <input type="text" name="first_name" required placeholder="First name" value="{{ old('first_name', auth()->user()->name ?? '') }}"
                                   class="field-input @error('first_name') has-error @enderror">
                            @error('first_name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <input type="text" name="last_name" required placeholder="Last name" value="{{ old('last_name') }}"
                                   class="field-input @error('last_name') has-error @enderror">
                            @error('last_name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="field">
                        <input type="text" name="address" required placeholder="Address" value="{{ old('address') }}"
                               class="field-input @error('address') has-error @enderror">
                        @error('address')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field-row">
                        <div>
                            <input type="text" name="city" required placeholder="City" value="{{ old('city') }}"
                                   class="field-input @error('city') has-error @enderror">
                            @error('city')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <input type="text" name="postal_code" placeholder="Postal code (optional)" value="{{ old('postal_code') }}" class="field-input">
                    </div>
                    <div class="field phone-wrap">
                        <span class="phone-prefix">🇵🇰 +92</span>
                        <input type="text" name="phone" required placeholder="300 0000000" value="{{ old('phone') }}"
                               class="field-input phone-input @error('phone') has-error @enderror">
                        @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="check-row" style="margin-top: 12px;">
                        <input type="checkbox" id="save_info" name="save_info">
                        <label for="save_info">Save this information for next time</label>
                    </div>
                </div>

                <!-- Shipping Method -->
                <div style="margin-bottom: 28px;">
                    <h2 class="section-title">Shipping method</h2>
                    <div class="radio-group">
                        <div class="radio-option selected">
                            <span class="option-label">LCS</span>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div style="margin-bottom: 28px;">
                    <h2 class="section-title">Payment</h2>
                    <p class="section-subtitle">All transactions are secure and encrypted.</p>

                    @if($hasStitched)
                        <div class="stitched-notice">
                            <strong>Note:</strong> Stitched items require advance payment. Cash on Delivery is not available for this order.
                        </div>
                    @endif

                    <div class="radio-group">
                        <!-- COD Option -->
                        <label class="radio-option"
                               :class="{ 'selected': paymentMethod === 'cod', 'disabled': hasStitched }"
                               @click="!hasStitched && (paymentMethod = 'cod')">
                            <div class="radio-left">
                                <input type="radio" name="payment_method" value="cod"
                                       x-model="paymentMethod" :disabled="hasStitched">
                                <span class="option-label">Cash on Delivery (COD)</span>
                            </div>
                        </label>

                        <!-- Credit Card Option -->
                        <label class="radio-option"
                               :class="{ 'selected': paymentMethod === 'stripe' }"
                               @click="paymentMethod = 'stripe'">
                            <div class="radio-left">
                                <input type="radio" name="payment_method" value="stripe"
                                       x-model="paymentMethod">
                                <span class="option-label">Credit card</span>
                            </div>
                            <div class="card-icons">
                                <!-- Visa -->
                                <svg width="38" height="24" viewBox="0 0 38 24" class="card-icon">
                                    <rect width="38" height="24" rx="3" fill="#1A1F71"/>
                                    <text x="19" y="15" text-anchor="middle" fill="#fff" font-size="9" font-weight="bold" font-family="Arial">VISA</text>
                                </svg>
                                <!-- MasterCard -->
                                <svg width="38" height="24" viewBox="0 0 38 24" class="card-icon">
                                    <rect width="38" height="24" rx="3" fill="#fff" stroke="#e5e5e5"/>
                                    <circle cx="15" cy="12" r="7" fill="#EB001B" opacity="0.9"/>
                                    <circle cx="23" cy="12" r="7" fill="#F79E1B" opacity="0.9"/>
                                    <circle cx="19" cy="12" r="4" fill="#FF5F00"/>
                                </svg>
                            </div>
                        </label>

                        <!-- Stripe Card Fields -->
                        <div x-show="paymentMethod === 'stripe'" x-cloak class="stripe-fields"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            <div>
                                <div id="card-number-element" class="StripeElement"></div>
                            </div>
                            <div class="stripe-row">
                                <div id="card-expiry-element" class="StripeElement"></div>
                                <div id="card-cvc-element" class="StripeElement"></div>
                            </div>
                            <div style="margin-top: 12px;">
                                <input type="text" name="card_name" placeholder="Name on card" class="field-input">
                            </div>
                            <p x-show="cardError" x-text="cardError" class="field-error" style="margin-top: 8px;" x-cloak></p>
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div style="margin-bottom: 16px;">
                    <h2 class="section-title">Billing address</h2>
                    <div class="radio-group">
                        <label class="radio-option selected" :class="{ 'selected': billingType === 'same' }" @click="billingType = 'same'">
                            <div class="radio-left">
                                <input type="radio" name="billing_type" value="same" x-model="billingType">
                                <span class="option-label">Same as shipping address</span>
                            </div>
                        </label>
                        <label class="radio-option" :class="{ 'selected': billingType === 'different' }" @click="billingType = 'different'">
                            <div class="radio-left">
                                <input type="radio" name="billing_type" value="different" x-model="billingType">
                                <span class="option-label">Use a different billing address</span>
                            </div>
                        </label>
                    </div>

                    <!-- Different Billing Address Fields -->
                    <div x-show="billingType === 'different'" x-cloak style="margin-top: 12px;"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="field-row">
                            <input type="text" name="billing_first_name" placeholder="First name" class="field-input">
                            <input type="text" name="billing_last_name" placeholder="Last name" class="field-input">
                        </div>
                        <div class="field">
                            <input type="text" name="billing_address" placeholder="Address" class="field-input">
                        </div>
                        <div class="field-row">
                            <input type="text" name="billing_city" placeholder="City" class="field-input">
                            <input type="text" name="billing_postal_code" placeholder="Postal code (optional)" class="field-input">
                        </div>
                    </div>
                </div>

                {{-- Payment error --}}
                <div x-show="paymentError" x-cloak class="payment-error" x-text="paymentError"></div>

                <button type="submit" id="checkout-btn" class="submit-btn"
                        :class="{ 'loading': submitting }" :disabled="submitting">
                    <span x-show="!submitting" x-text="paymentMethod === 'stripe' ? 'Pay now' : 'Complete order'">Complete order</span>
                    <span x-show="submitting" x-cloak>Processing...</span>
                </button>
            </form>
        </div>

        <!-- RIGHT COLUMN: Order Summary -->
        <div class="checkout-right">
            @php $cart = session('cart', []); @endphp

            @foreach($cart as $item)
                <div class="summary-item">
                    <div class="summary-img">
                        @php
                            $imgUrl = null;
                            // Try multiple image path strategies for local + production
                            if (!empty($item['image'])) {
                                $imgUrl = $item['image'];
                            } elseif (!empty($item['image_path'])) {
                                $imgUrl = asset('storage/' . $item['image_path']);
                            } elseif (!empty($item['product_id'])) {
                                $product = \App\Models\Product::find($item['product_id']);
                                if ($product && $product->image_path) {
                                    $imgUrl = asset('storage/' . $product->image_path);
                                }
                            }
                        @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $item['name'] }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div class="img-fallback" style="display:none;">No Image</div>
                        @else
                            <div class="img-fallback">No Image</div>
                        @endif
                        <div class="qty-badge">{{ $item['quantity'] }}</div>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div class="summary-name">{{ $item['name'] }}</div>
                        <div class="summary-variant">
                            @if(!empty($item['type'])){{ $item['type'] }} / @endif{{ $item['size'] ?? 'Default' }}
                        </div>
                    </div>
                    <div class="summary-price">Rs {{ number_format($item['price'] * $item['quantity']) }}.00</div>
                </div>
            @endforeach

            <hr class="summary-divider">

            <div class="summary-row">
                <span class="label">Subtotal</span>
                <span>Rs {{ number_format($item_total) }}.00</span>
            </div>
            <div class="summary-row">
                <span class="label">Shipping</span>
                <span x-text="paymentMethod === 'cod' ? 'Rs 250' : 'Rs 200'">FREE</span>
            </div>

            <hr class="summary-divider">

            <div class="summary-row total">
                <span>Total</span>
                <span><span class="pkr">PKR</span> Rs <span x-text="total.toLocaleString() + '.00'">{{ number_format($item_total) }}.00</span></span>
            </div>
        </div>
    </div>

    <script>
    function checkoutApp() {
        return {
            paymentMethod: '{{ $hasStitched ? "stripe" : "cod" }}',
            hasStitched: {{ $hasStitched ? 'true' : 'false' }},
            billingType: 'same',
            submitting: false,
            paymentIntentId: '',
            cardError: '',
            paymentError: '',
            stripe: null,
            cardElement: null,
            subtotal: {{ $item_total }},

            get shipping() {
                return this.paymentMethod === 'cod' ? 250 : 200;
            },
            get total() {
                return this.subtotal + this.shipping;
            },

            init() {
                const stripeKey = '{{ $stripeKey ?? "" }}';
                if (stripeKey && stripeKey !== '') {
                    this.stripe = Stripe(stripeKey);
                    const elements = this.stripe.elements();
                    const elementStyle = {
                        base: {
                            fontFamily: 'Outfit, sans-serif',
                            fontSize: '13px',
                            color: '#333',
                            '::placeholder': { color: '#999' },
                        },
                        invalid: { color: '#e53935' },
                    };

                    this.cardElement = elements.create('cardNumber', {
                        style: elementStyle, placeholder: 'Card number',
                    });
                    this.cardElement.mount('#card-number-element');

                    const cardExpiry = elements.create('cardExpiry', {
                        style: elementStyle, placeholder: 'Expiration date (MM / YY)',
                    });
                    cardExpiry.mount('#card-expiry-element');

                    const cardCvc = elements.create('cardCvc', {
                        style: elementStyle, placeholder: 'Security code',
                    });
                    cardCvc.mount('#card-cvc-element');

                    const handleError = (event) => {
                        this.cardError = event.error ? event.error.message : '';
                    };
                    this.cardElement.on('change', handleError);
                    cardExpiry.on('change', handleError);
                    cardCvc.on('change', handleError);
                }
            },

            async handleSubmit(e) {
                if (this.submitting) return;
                this.paymentError = '';
                this.cardError = '';

                const form = document.getElementById('checkout-form');
                if (!form.reportValidity()) return;

                this.submitting = true;

                if (this.paymentMethod === 'stripe') {
                    await this.processCardPayment(form);
                } else {
                    form.submit();
                }
            },

            async processCardPayment(form) {
                if (!this.stripe || !this.cardElement) {
                    this.paymentError = 'Payment system is not configured. Please contact support or choose Cash on Delivery.';
                    this.submitting = false;
                    return;
                }

                try {
                    const response = await fetch('{{ route("checkout.paymentIntent") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (data.error) {
                        this.paymentError = data.error;
                        this.submitting = false;
                        return;
                    }

                    const { error, paymentIntent } = await this.stripe.confirmCardPayment(data.clientSecret, {
                        payment_method: {
                            card: this.cardElement,
                            billing_details: {
                                name: form.querySelector('[name="first_name"]').value + ' ' + form.querySelector('[name="last_name"]').value,
                                email: form.querySelector('[name="email"]').value,
                                phone: form.querySelector('[name="phone"]').value,
                            },
                        },
                    });

                    if (error) {
                        this.cardError = error.message;
                        this.submitting = false;
                        return;
                    }

                    if (paymentIntent.status === 'succeeded') {
                        this.paymentIntentId = paymentIntent.id;
                        await this.$nextTick();
                        form.submit();
                    } else {
                        this.paymentError = 'Payment was not successful. Please try again.';
                        this.submitting = false;
                    }

                } catch (err) {
                    console.error('Payment error:', err);
                    this.paymentError = 'An unexpected error occurred. Please try again.';
                    this.submitting = false;
                }
            },
        };
    }
    </script>
</body>
</html>
