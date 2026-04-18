<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Oxy-bliss</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
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
            z-index: 100;
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

        /* Login Card / Account Box */
        .main-content {
            margin-top: 180px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 50px;
        }

        .account-card {
            width: 100%;
            max-width: 500px;
            padding: 40px;
            text-align: left;
            background: #fff;
            border: 1px solid #eee;
        }

        .account-title {
            font-size: 1.25rem;
            font-weight: 400;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .info-group {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #999;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: #111;
        }

        .account-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .account-nav li {
            margin-bottom: 1rem;
        }

        .account-nav a, .account-nav button {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: #111;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.2s;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            width: 100%;
            text-align: left;
            font-family: inherit;
        }

        .account-nav a:hover, .account-nav button:hover {
            color: #666;
        }

        .account-nav svg {
            color: #999;
        }

        @media (max-width: 768px) {
            nav {
                position: sticky !important;
                top: 40px !important; /* Sit below fixed ticker */
                z-index: 1001;
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
                z-index: 20;
            }
            .nav-links, .search-bar {
                display: none;
            }
            .main-content { margin-top: 100px; padding-bottom: 30px; }
            .account-card { padding: 15px; }
        }
    </style>
</head>
<body x-data="{ ...cartSystem(), menuOpen: false }" x-init="initCart()">
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
            <div class="ticker-item"><span>🔥</span> Ceremonial Collection '26</div>
            <div class="ticker-item"><span>🔥</span> Summer New Arrivals</div>
            <div class="ticker-item"><span>🔥</span> Ceremonial Collection '26</div>
            <div class="ticker-item"><span>🔥</span> Summer New Arrivals</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav>
        <div class="hamburger" @click="menuOpen = true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </div>
        
        <a href="/" class="logo" style="position: static; transform: none; margin: 0 auto;">Oxy-Bliss</a>

        <div class="nav-auth">
            <div class="search-bar">
                <form action="{{ route('shop') }}" method="GET" style="display: flex; align-items: center;">
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit" style="background: none; border: none; cursor: pointer; display: flex; align-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                </form>
            </div>

            <a href="{{ route('account') }}" style="pointer-events: auto !important; cursor: pointer;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events: none;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </a>

            <a href="#" style="pointer-events: auto !important; cursor: pointer;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events: none;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.82-8.82 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </a>
            
            <div class="cart-icon" @click="cartOpen = true" @touchstart.passive="cartOpen = true" style="pointer-events: auto !important; cursor: pointer;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="pointer-events: none;"><path d="M9 20a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm1-8H7a2 2 0 0 1-2-2V6H4V4h2l.5 2H20l-1 7a2 2 0 0 1-2 2z"></path></svg>
                <span class="cart-count" x-text="Object.keys(cart).length" style="pointer-events: none;"></span>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="main-content">
        <div class="account-card">
            <h2 class="account-title">My Account</h2>

            <div class="info-group">
                <div class="info-label">Email</div>
                <div class="info-value">{{ auth()->user()->email }}</div>
            </div>

            <div class="info-group">
                <div class="info-label">Options</div>
                <ul class="account-nav">
                    <li>
                        <a href="{{ route('order.history') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                            My Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            Profile Settings
                        </a>
                    </li>
                </ul>
            </div>

            <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem;">
                @csrf
                <button type="submit" style="width: 100%; background: #111; color: white; border: none; padding: 1.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: background 0.3s; font-size: 0.75rem;">
                    Logout
                </button>
            </form>
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
                    this.total = Object.values(this.cart).reduce((sum, item) => sum + (item.price * item.quantity), 0);
                }
            }
        }
    </script>
</body>
</html>
