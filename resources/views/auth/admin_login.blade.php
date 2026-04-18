<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Oxy-bliss</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Outfit', sans-serif;
            background: 
                radial-gradient(circle at 0% 0%, #b3e5fc 0%, transparent 50%),
                radial-gradient(circle at 100% 0%, #fff9c4 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, #e0f2f1 0%, transparent 50%),
                radial-gradient(circle at 0% 100%, #e1f5fe 0%, transparent 50%),
                linear-gradient(to bottom right, #80deea, #4dd0e1, #26c6da);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            position: relative;
            padding: 40px 0;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.1;
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 10;
            padding: 20px;
        }

        .logo-wrapper {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .logo-circle {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 0 auto 1.25rem;
            padding: 12px;
        }

        .store-name {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a3a3a;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin: 0;
            line-height: 1;
        }

        .store-tagline {
            font-size: 0.85rem;
            color: #2d5a5a;
            letter-spacing: 1.5px;
            margin-top: 0.5rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            padding: 3.5rem;
            width: 100%;
            box-shadow: 0 30px 60px rgba(0,0,0,0.12);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .login-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1a3a3a;
            text-align: center;
            margin-bottom: 2.5rem;
            letter-spacing: -0.5px;
            text-transform: lowercase;
        }

        .form-group {
            margin-bottom: 1.75rem;
        }

        .label-text {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #88a0a0;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-field {
            width: 100%;
            padding: 1.1rem;
            border: 1.5px solid #eef2f2;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: #1a3a3a;
            outline: none;
            box-sizing: border-box;
            background: #fcfdfe;
        }

        .input-field:focus {
            border-color: #4db6ac;
            background: white;
            box-shadow: 0 0 0 4px rgba(77, 182, 172, 0.1);
        }

        .password-row {
            display: flex;
            gap: 0.75rem;
            align-items: stretch;
        }

        .login-btn {
            background: linear-gradient(135deg, #4db6ac, #26a69a);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0 1.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.75rem;
            box-shadow: 0 4px 12px rgba(38, 166, 154, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(38, 166, 154, 0.4);
            filter: brightness(1.05);
        }

        .input-error {
            font-size: 0.75rem;
            color: #e57373;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .corner-star {
            position: absolute;
            bottom: 40px;
            right: 40px;
            width: 50px;
            height: 50px;
            opacity: 0.2;
            animation: pulse 4s infinite alternate;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a3a3a;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.1rem;
            letter-spacing: -2px;
            line-height: 1;
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

        @keyframes pulse {
            from { transform: scale(1); opacity: 0.2; }
            to { transform: scale(1.1); opacity: 0.3; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-wrapper">
            <div class="logo-circle">
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 15C35 15 20 25 15 45C10 65 25 85 50 85C75 85 90 65 85 45C80 25 65 15 50 15Z" fill="#a7d7c5"/>
                    <path d="M50 20C40 20 30 25 25 40C20 55 30 75 50 75C70 75 80 55 75 40C70 25 60 20 50 20Z" fill="#2d5a5a"/>
                    <path d="M50 25C55 35 45 45 50 55C55 65 45 75 40 70C35 65 45 55 40 45C35 35 45 25 50 25Z" fill="#ffffff" opacity="0.6"/>
                    <circle cx="50" cy="50" r="8" fill="#ffffff"/>
                    <path d="M52 48L58 50L52 52L50 58L48 52L42 50L48 48L50 42L52 48Z" fill="#2d5a5a"/>
                </svg>
            </div>
            <a href="/" class="logo">Oxy-Bliss</a>
            <p class="store-tagline">Your Daily Dose of Joy</p>
        </div>

        <div class="login-card">
            <h2 class="login-title">login only for admin</h2>

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="form-group">
                    <label class="label-text">Email Address</label>
                    <input type="email" name="email" class="input-field" required autofocus autocomplete="off">
                    @if($errors->has('email'))
                        <div class="input-error">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="label-text">Password</label>
                    <div class="password-row">
                        <input type="password" name="password" class="input-field" required autocomplete="off">
                        <button type="submit" class="login-btn">Log In</button>
                    </div>
                    @if($errors->has('password'))
                        <div class="input-error">{{ $errors->first('password') }}</div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="corner-star">
        <svg viewBox="0 0 100 100" fill="#ffffff">
            <path d="M50 0L60 40L100 50L60 60L50 100L40 60L0 50L40 40Z"/>
        </svg>
    </div>
</body>
</html>
