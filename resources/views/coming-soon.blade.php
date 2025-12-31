<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Coming Soon - {{ config('app.name', 'Lomoofy') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 2rem;
            max-width: 800px;
            z-index: 1;
            position: relative;
        }

        .logo {
            margin-bottom: 2rem;
        }

        .logo img {
            max-width: 200px;
            height: auto;
            filter: brightness(0) invert(1);
        }

        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            animation: fadeInUp 0.8s ease-out;
        }

        .subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .message {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 3rem;
            opacity: 0.85;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .circle:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -50px;
            right: -50px;
            animation-delay: 5s;
        }

        .circle:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
            animation-delay: 10s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(30px, -30px) scale(1.1);
            }
            66% {
                transform: translate(-20px, 20px) scale(0.9);
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }

            .subtitle {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="background-animation">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <div class="container">
        @php
            $companySettings = \App\Models\CompanySetting::first();
            $companyName = $companySettings->company_name ?? 'Lomoofy';
            $companyLogo = $companySettings->company_logo ? asset('storage/' . $companySettings->company_logo) : null;
        @endphp

        @if($companyLogo)
            <div class="logo">
                <img src="{{ $companyLogo }}" alt="{{ $companyName }}">
            </div>
        @endif

        <h1>Coming Soon</h1>
        <p class="subtitle">We're working on something amazing!</p>
        <p class="message">
            Our website is currently under construction. We're putting the finishing touches on a brand new experience 
            that will be worth the wait. Stay tuned for updates!
        </p>
    </div>
</body>
</html>
