<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Painel - SIGEF</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: #0a1628;
            background-image: url('/images/login-bg.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            width: 100%;
            max-width: 900px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-container {
            margin-bottom: 2rem;
            text-align: center;
        }

        .logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
        }

        .brand-title {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            margin-top: 1rem;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }

        .welcome-text {
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 400;
            margin-top: 0.5rem;
            text-align: center;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        .panels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            width: 100%;
            margin-top: 2rem;
        }

        .panel-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .panel-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .panel-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .panel-icon.admin { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
        .panel-icon.escola { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .panel-icon.dpq { background: linear-gradient(135deg, #10b981, #059669); }
        .panel-icon.comando { background: linear-gradient(135deg, #f59e0b, #d97706); }

        .panel-icon svg {
            width: 32px;
            height: 32px;
            color: white;
        }

        .panel-name {
            color: #ffffff;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .panel-desc {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.875rem;
        }

        .logout-btn {
            margin-top: 3rem;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: rgba(255, 255, 255, 0.7);
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.5);
        }

        .logout-btn svg {
            width: 18px;
            height: 18px;
        }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.75rem;
        }

        @media (max-width: 640px) {
            .panels-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-text {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="/images/logo-policia.png" alt="SIGEF" class="logo" onerror="this.style.display='none'">
            <h1 class="brand-title">SIGEF</h1>
            <p class="welcome-text">Olá, {{ $user->name ?? 'Utilizador' }}!</p>
            <p class="subtitle">Selecione o painel que deseja aceder</p>
        </div>

        <div class="panels-grid">
            @foreach($panels as $panelId => $panel)
                <a href="{{ $panel['url'] }}" class="panel-card">
                    <div class="panel-icon {{ $panelId }}">
                        @if($panelId === 'admin')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        @elseif($panelId === 'escola')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                            </svg>
                        @elseif($panelId === 'dpq')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        @elseif($panelId === 'comando')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        @endif
                    </div>
                    <h3 class="panel-name">{{ $panel['name'] }}</h3>
                    <p class="panel-desc">Clique para aceder</p>
                </a>
            @endforeach
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                Terminar Sessão
            </button>
        </form>

        <p class="footer-text">
            &copy; {{ date('Y') }} SIGEF - Polícia Nacional de Angola
        </p>
    </div>
</body>
</html>
