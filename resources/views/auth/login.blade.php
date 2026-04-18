<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Oxy-bliss</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary: #000000;
            --bg: #ffffff;
            --gray-light: #f8f8f8;
            --gray-medium: #e5e5e5;
            --text-muted: #666666;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--primary);
            line-height: 1.5;
        }

        /* Ticker Announcement Bar */
        .ticker-wrap {
            width: 100%;
            background-color: #000;
            color: #fff;
            overflow: hidden;
            height: 40px;
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .ticker-move {
            display: flex;
            white-space: nowrap;
            animation: ticker-animation 30s linear infinite;
            width: max-content;
            will-change: transform;
        }

        .ticker-item {
            display: flex;
            align-items: center;
            padding: 0 4rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        @keyframes ticker-animation {
            0% { transform: translateX(-50%); }
            100% { transform: translateX(0); }
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            background: white;
            position: fixed;
            top: 40px;
            left: 0;
            right: 0;
            z-index: 9991;
            border-bottom: 1px solid #eee;
            height: 80px;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 400;
            letter-spacing: -1px;
            text-decoration: none;
            color: #000;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 0.1rem;
        }

        .logo-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-leaf {
            position: absolute;
            width: 0.55em;
            height: 0.55em;
            fill: currentColor;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .nav-auth {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-left: auto;
        }

        .nav-auth a, .hamburger, .cart-icon {
            color: #000;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: transparent;
            padding: 0.6rem 0;
            border: none;
            color: #000;
        }

        .search-bar input {
            background: transparent;
            border: none;
            border-bottom: 1px solid #eee;
            outline: none;
            font-size: 0.85rem;
            width: 0;
            color: #000;
            transition: width 0.3s;
            padding: 0;
        }

        .search-bar:hover input, .search-bar input:focus {
            width: 150px;
            padding: 0 0.5rem;
        }

        .cart-icon {
            position: relative;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background: black;
            color: white;
            font-size: 0.6rem;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        /* Cart & Menu Drawers */
        .cart-drawer, .menu-drawer, .wishlist-drawer {
            position: fixed !important;
            top: 0;
            height: 100vh;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            z-index: 9999 !important;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            nav {
                position: sticky !important;
                top: 40px !important;
                z-index: 9995 !important;
                background: #fff;
                box-shadow: 0 1px 8px rgba(0,0,0,0.06);
            }
            .logo { 
                font-size: 1.35rem !important; 
                font-weight: 700 !important;
                z-index: 10;
            }
            .nav-auth {
                position: relative;
                z-index: 9996 !important;
            }
            .nav-links, .search-bar {
                display: none;
            }
            .main-content { margin-top: 100px; padding-bottom: 30px; }
            .login-card { padding: 15px; }
        }

        .cart-drawer { right: -400px; width: 400px; }
        .cart-drawer.open { right: 0 !important; }
        .wishlist-drawer { right: -400px; width: 400px; }
        .wishlist-drawer.open { right: 0 !important; }
        .menu-drawer { left: -350px; width: 350px; }
        .menu-drawer.open { left: 0 !important; }

        .cart-overlay, .menu-overlay {
            position: fixed !important;
            inset: 0;
            background: rgba(0,0,0,0.3);
            z-index: 9998 !important;
            display: none;
        }

        .cart-overlay.open, .menu-overlay.open { display: block; }

        /* Login Card */
        .main-content {
            margin-top: 180px; /* Spacer for fixed header/ticker */
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 50px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            text-align: center;
        }

        .login-title {
            font-size: 1.25rem;
            font-weight: 400;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 1.5rem;
        }

        .login-subtitle {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 2.5rem;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .input-field {
            width: 100%;
            padding: 1.2rem;
            border: 1px solid #e5e5e5;
            font-size: 0.9rem;
            transition: border-color 0.3s;
            color: #000;
            outline: none;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        .input-field:focus {
            border-color: #000;
        }

        .login-btn {
            width: 100%;
            background: #111;
            color: white;
            border: none;
            padding: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 0.75rem;
            margin-top: 1rem;
        }

        .login-btn:hover {
            background: #333;
        }

        .signup-link {
            margin-top: 2.5rem;
            font-size: 0.85rem;
            color: #666;
            letter-spacing: 0.5px;
        }

        .signup-link a {
            color: #000;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .input-error {
            font-size: 0.75rem;
            color: #e63946;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .cart-drawer { width: 100% !important; right: -100% !important; max-width: 100vw; }
            .wishlist-drawer { width: 100% !important; right: -100% !important; max-width: 100vw; }
        }
    </style>
</head>
<body x-data="{ ...cartSystem(), ...wishlistSystem(), menuOpen: false, wishlistOpen: false }" x-init="initCart(); initWishlist();">
    <template x-if="toast && toast.show">
        <div style="position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%); background: #111; color: white; padding: 0.75rem 1.5rem; font-size: 0.75rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; border-radius: 4px; z-index: 2000; box-shadow: 0 5px 20px rgba(0,0,0,0.2);" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4 -translate-x-50" x-transition:enter-end="opacity-100 transform translate-y-0 -translate-x-50" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0 -translate-x-50" x-transition:leave-end="opacity-0 transform translate-y-4 -translate-x-50">
            <span x-text="toast.message"></span>
        </div>
    </template>
    <!-- Announcement Bar Ticker -->
    <div class="ticker-wrap">
        <div class="ticker-move">
            <div class="ticker-item"><span>🔥</span> Ceremonial Collection '26</div>
            <div class="ticker-item"><span>🔥</span> Summer New Arrivals</div>
            <div class="ticker-item"><span>🔥</span> Ceremonial Collection '26</div>
            <div class="ticker-item"><span>🔥</span> Summer New Arrivals</div>
            <div class="ticker-item"><span>🔥</span> Ceremonial Collection '26</div>
            <div class="ticker-item"><span>🔥</span> Summer New Arrivals</div>
            <div class="ticker-item"><span>🔥</span> Ceremonial Collection '26</div>
            <div class="ticker-item"><span>🔥</span> Summer New Arrivals</div>
            <!-- Duplicate for loop -->
            <div class="ticker-item"><span>🔥</span> Ceremonial Collection '26</div>
            <div class="ticker-item"><span>🔥</span> Summer New Arrivals</div>
            <div class="ticker-item"><span>🔥</span> Ceremonial Collection '26</div>
            <div class="ticker-item"><span>🔥</span> Summer New Arrivals</div>
        </div>
    </div>

    <!-- Cart Drawer & Overlay -->
    <div class="cart-overlay" :class="{ 'open': cartOpen }" @click="cartOpen = false"></div>
    <div class="cart-drawer" :class="{ 'open': cartOpen }">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 style="text-transform: uppercase; letter-spacing: 1px;">Shopping Bag</h3>
            <button @click="cartOpen = false" style="background:none; border:none; font-size: 1.5rem; cursor:pointer;">&times;</button>
        </div>
        
        <div style="flex: 1; overflow-y: auto;">
            <template x-for="(item, id) in cart" :key="id">
                <div class="cart-item" style="display: flex; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid var(--gray-medium);">
                    <img :src="item.image || '/placeholder.png'" alt="" style="width: 80px; height: 100px; object-fit: cover; background: var(--gray-light);">
                    <div style="flex: 1;">
                        <p x-text="item.name" style="font-size: 0.9rem; font-weight: 500;"></p>
                        <p style="font-size: 0.75rem; color: #666;" x-text="'Size: ' + item.size"></p>
                        <template x-if="item.color">
                            <p style="font-size: 0.75rem; color: #666;" x-text="'Color: ' + item.color"></p>
                        </template>
                        <p style="font-size: 0.85rem; margin-top: 0.5rem;" x-text="'Rs. ' + item.price"></p>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem;">
                            <button @click="updateQuantity(id, item.quantity - 1)" style="border:1px solid #ddd; width:24px; height:24px; display:flex; align-items:center; justify-content:center; background:#fff;">-</button>
                            <span x-text="item.quantity" style="font-size: 0.85rem;"></span>
                            <button @click="updateQuantity(id, item.quantity + 1)" style="border:1px solid #ddd; width:24px; height:24px; display:flex; align-items:center; justify-content:center; background:#fff;">+</button>
                            <button @click="removeItem(id)" style="margin-left:auto; font-size: 0.75rem; color: red; background:none; border:none; cursor:pointer;">Remove</button>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="Object.keys(cart).length === 0" style="text-align:center; margin-top: 4rem; color: #999;">
                Your bag is empty
            </div>
        </div>

        <div class="cart-total" x-show="Object.keys(cart).length > 0" style="margin-top: auto; padding-top: 2rem; display: flex; justify-content: space-between; font-weight: 600;">
            <span>Subtotal</span>
            <span x-text="'Rs. ' + total"></span>
        </div>
        
        <a href="{{ route('checkout') }}" x-show="Object.keys(cart).length > 0" class="shop-btn" style="text-align: center; margin-top: 1.5rem; display: inline-block; padding: 0.7rem 1.8rem; background: black; color: white; text-decoration: none; text-transform: uppercase; font-size: 0.65rem; font-weight: 600; letter-spacing: 3px;">Checkout</a>
    </div>

    <!-- Wishlist Drawer & Overlay -->
    <div class="cart-overlay" :class="{ 'open': wishlistOpen }" @click="wishlistOpen = false"></div>
    <div class="cart-drawer" :class="{ 'open': wishlistOpen }">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 style="text-transform: uppercase; letter-spacing: 1px;">Your Favorites</h3>
            <button @click="wishlistOpen = false" style="background:none; border:none; font-size: 1.5rem; cursor:pointer;">&times;</button>
        </div>
        
        <div style="flex: 1; overflow-y: auto;">
            <template x-for="item in wishlistItems" :key="item.id">
                <div class="cart-item" style="display: flex; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid var(--gray-medium);">
                    <img :src="item.image" alt="" style="width: 80px; height: 100px; object-fit: cover; background: var(--gray-light);">
                    <div style="flex: 1;">
                        <a :href="item.url" style="text-decoration:none; color:inherit;">
                            <p x-text="item.name" style="font-size: 0.9rem; font-weight: 500;"></p>
                        </a>
                        <p style="font-size: 0.85rem; margin-top: 0.5rem;" x-text="'Rs. ' + item.price"></p>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem;">
                            <button @click="toggleWishlist(item.id); fetchWishlistDetails();" style="font-size: 0.75rem; color: #999; background:none; border:none; cursor:pointer; text-decoration: underline;">Remove</button>
                            <a :href="item.url" class="cat-shop-btn" style="padding: 0.4rem 0.8rem; font-size: 0.5rem; background: #e63946; color: white; text-decoration: none; text-transform: uppercase; letter-spacing: 1px;">View Product</a>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="wishlistItems.length === 0" style="text-align:center; margin-top: 4rem; color: #999;">
                You haven't saved any items yet
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav>
        <div class="hamburger" @click="menuOpen = true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </div>
        
        <a href="/" class="logo">Oxy-Bliss</a>

        <div class="nav-auth">
            <div class="search-bar">
                <form action="{{ route('shop') }}" method="GET" style="display: flex; align-items: center;">
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit" style="background: none; border: none; cursor: pointer; display: flex; align-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                </form>
            </div>

            @auth
                <a href="{{ route('account') }}" style="pointer-events: auto !important; cursor: pointer;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events: none;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </a>
            @else
                <a href="{{ route('login') }}" style="pointer-events: auto !important; cursor: pointer;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events: none;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </a>
            @endauth

            <a href="#" @click.prevent="wishlistOpen = true; fetchWishlistDetails();" @touchstart.passive="wishlistOpen = true; fetchWishlistDetails();" style="pointer-events: auto !important; cursor: pointer; position: relative;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events: none;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.82-8.82 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <span class="cart-count" x-text="wishlist.length" x-show="wishlist.length > 0" style="pointer-events: none; top: -8px; right: -10px;"></span>
            </a>
            
            <div class="cart-icon" @click="cartOpen = true" @touchstart.passive="cartOpen = true" style="pointer-events: auto !important; cursor: pointer;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events: none;"><path d="M9 20a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm1-8H7a2 2 0 0 1-2-2V6H4V4h2l.5 2H20l-1 7a2 2 0 0 1-2 2z"></path></svg>
                <span class="cart-count" x-text="Object.keys(cart).length" style="pointer-events: none;"></span>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="main-content">
        <div class="login-card">
            <h2 class="login-title">Login</h2>
            <p class="login-subtitle">Enter your email and password to login:</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <input type="email" name="email" class="input-field" placeholder="E-mail" required autofocus autocomplete="email" value="{{ old('email') }}">
                    @if($errors->has('email'))
                        <div class="input-error">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <div class="form-group" style="margin-top: 2rem; position: relative;">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="position: absolute; right: 0; top: -25px; font-size: 0.75rem; color: #888; text-decoration: none;">Forgot your password?</a>
                    @endif
                    <input type="password" name="password" class="input-field" placeholder="Password" required autocomplete="current-password">
                    @if($errors->has('password'))
                        <div class="input-error">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <div class="signup-link">
                Don't have an account? <a href="{{ route('register') }}">Sign up</a>
            </div>
        </div>
    </div>

    <!-- Scripts (Functional Cart) -->
    <script>
        function cartSystem() {
            return {
                cart: {},
                total: 0,
                cartOpen: false,
                initCart() {
                    fetch('/cart', { headers: { 'Accept': 'application/json' } })
                        .then(res => res.json())
                        .then(data => { this.cart = data; this.calculateTotal(); });
                },
                calculateTotal() {
                    this.total = Object.values(this.cart).reduce((sum, item) => sum + (item.price * item.quantity), 0).toLocaleString();
                },
                updateQuantity(id, qty) {
                    if (qty < 1) return this.removeItem(id);
                    fetch(`/cart/update/${id}`, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ quantity: qty })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.cart = data.cart;
                            this.calculateTotal();
                        }
                    });
                },
                removeItem(id) {
                    fetch(`/cart/remove/${id}`, {
                        method: 'DELETE',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.cart = data.cart;
                            this.calculateTotal();
                        }
                    });
                }
            }
        }

        function wishlistSystem() {
            return {
                wishlist: [],
                toast: { show: false, message: '' },
                initWishlist() {
                    const saved = localStorage.getItem('oxy_bliss_wishlist');
                    if (saved) {
                        this.wishlist = JSON.parse(saved);
                    }
                },
                toggleWishlist(productId) {
                    const index = this.wishlist.indexOf(productId);
                    if (index === -1) {
                        this.wishlist.push(productId);
                        this.showToast('Added to Favorites');
                    } else {
                        this.wishlist.splice(index, 1);
                        this.showToast('Removed from Favorites');
                    }
                    localStorage.setItem('oxy_bliss_wishlist', JSON.stringify(this.wishlist));
                },
                wishlistItems: [],
                fetchWishlistDetails() {
                    if (this.wishlist.length === 0) {
                        this.wishlistItems = [];
                        return;
                    }
                    fetch('/wishlist/details', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: this.wishlist })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.wishlistItems = data;
                    });
                },
                showToast(message) {
                    this.toast.message = message;
                    this.toast.show = true;
                    setTimeout(() => this.toast.show = false, 3000);
                }
            }
        }
    </script>
</body>
</html>
