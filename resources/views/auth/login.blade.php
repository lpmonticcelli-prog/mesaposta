<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mesa Posta – Login Administrativo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700,900|figtree:400,500,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mesa: {
                            dark: '#111111',
                            black: '#0A0A0A',
                            gold: '#D4AF37',
                            goldHover: '#AA882C',
                            gray: '#1E1E1E'
                        }
                    },
                    fontFamily: {
                        serif: ['Playfair Display', 'serif'],
                        sans: ['Figtree', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        .gold-gradient {
            background: linear-gradient(135deg, #D4AF37 0%, #F3E5AB 50%, #AA882C 100%);
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-200 bg-mesa-black min-h-screen flex items-center justify-center relative overflow-hidden p-4">

    <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] bg-mesa-gold/5 rounded-full filter blur-3xl"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[600px] h-[600px] bg-mesa-dark/50 rounded-full filter blur-3xl"></div>

    <div class="w-full max-w-md bg-mesa-dark/90 border border-mesa-gold/20 rounded-3xl p-8 backdrop-blur-xl shadow-2xl relative z-10">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full border border-mesa-gold/30 bg-mesa-black mb-4 shadow-inner">
                <svg class="w-8 h-8 text-mesa-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 3v18M3 12h18M12 3a9 9 0 110 18 9 9 0 010-18z" />
                </svg>
            </div>
            <h1 class="text-2xl font-serif font-black tracking-widest text-white uppercase">Mesa Posta</h1>
            <p class="text-[10px] font-bold text-mesa-gold uppercase tracking-widest mt-1">Ecosistema de Logística & ERP</p>
        </div>

        @if (session('status'))
            <div class="mb-4 text-sm font-bold text-green-500 bg-green-500/10 border border-green-500/20 p-3 rounded-lg text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block text-[10px] font-black text-mesa-gold uppercase tracking-widest mb-2">Credencial de Acesso</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="usuario@mesaposta.com"
                       class="block w-full rounded-xl border border-white/10 bg-mesa-black/50 text-white placeholder-gray-600 focus:border-mesa-gold focus:ring focus:ring-mesa-gold/20 focus:outline-none font-medium px-4 py-3.5 transition-all">
                @if($errors->get('email'))
                    <p class="mt-2 text-xs font-bold text-red-500">{{ $errors->first('email') }}</p>
                @endif
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="block text-[10px] font-black text-mesa-gold uppercase tracking-widest">Chave de Segurança</label>
                    @if (Route::has('password.request'))
                        <a class="text-[9px] font-bold text-gray-400 hover:text-mesa-gold transition-colors uppercase tracking-widest" href="{{ route('password.request') }}">
                            Recuperar Chave
                        </a>
                    @endif
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       placeholder="••••••••••••"
                       class="block w-full rounded-xl border border-white/10 bg-mesa-black/50 text-white placeholder-gray-600 focus:border-mesa-gold focus:ring focus:ring-mesa-gold/20 focus:outline-none font-medium px-4 py-3.5 transition-all">
                @if($errors->get('password'))
                    <p class="mt-2 text-xs font-bold text-red-500">{{ $errors->first('password') }}</p>
                @endif
            </div>

            <div class="flex items-center">
                <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                    <input id="remember_me" type="checkbox" name="remember" 
                           class="rounded border-white/20 bg-mesa-black text-mesa-gold focus:ring-offset-mesa-black focus:ring-mesa-gold">
                    <span class="ms-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Manter terminal conectado</span>
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-4 px-4 rounded-xl shadow-xl text-xs font-black text-mesa-black gold-gradient hover:opacity-90 active:scale-[0.98] focus:outline-none uppercase tracking-widest transition-all">
                    Autenticar Operador
                </button>
            </div>
        </form>
    </div>

</body>
</html>