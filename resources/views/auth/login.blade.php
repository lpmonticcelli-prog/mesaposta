<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acesso Restrito - Mesa Posta ERP</title>
    
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            dark: '#111111',
                            black: '#0a0a0a',
                            gold: '#ffc20c',
                            hover: '#e0a800'
                        }
                    },
                    fontFamily: { sans: ['Figtree', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-brand-black min-h-screen flex items-center justify-center relative overflow-hidden">

    {{-- IMAGEM DE FUNDO COM MÁSCARA ESCURA --}}
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?q=80&w=2069&auto=format&fit=crop" class="w-full h-full object-cover object-center opacity-30" alt="Mesa Posta Background">
        <div class="absolute inset-0 bg-gradient-to-t from-brand-black via-brand-dark/80 to-transparent"></div>
    </div>

    {{-- CAIXA DE LOGIN PREMIUM --}}
    <div class="relative z-10 w-full max-w-md px-8 py-10 bg-brand-dark/80 backdrop-blur-lg shadow-2xl rounded-2xl border border-gray-800 mx-4">
        
        {{-- CABEÇALHO DO LOGIN --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-brand-gold tracking-widest uppercase mb-2 drop-shadow-md">Mesa Posta</h1>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sistema ERP de Logística & Comercial</p>
        </div>

        {{-- ALERTAS DE ERRO (Senha incorreta, etc) --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-900/40 border border-red-500/50 p-4 rounded-lg">
                <ul class="text-[11px] font-black text-red-400 uppercase tracking-wider list-disc list-inside px-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-6 text-[11px] font-black text-green-400 uppercase tracking-wider text-center">
                {{ session('status') }}
            </div>
        @endif

        {{-- FORMULÁRIO DE ACESSO --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            {{-- CAMPO E-MAIL --}}
            <div>
                <label for="email" class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">E-mail Corporativo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="w-full bg-brand-black/50 border border-gray-700 text-white px-4 py-4 rounded-lg shadow-inner font-bold text-sm focus:border-brand-gold focus:ring-1 focus:ring-brand-gold transition-colors outline-none placeholder-gray-600"
                    placeholder="exemplo@mesaposta.com">
            </div>

            {{-- CAMPO SENHA --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="block text-[10px] font-black text-gray-500 uppercase tracking-widest">Senha de Segurança</label>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full bg-brand-black/50 border border-gray-700 text-white px-4 py-4 rounded-lg shadow-inner font-bold text-sm focus:border-brand-gold focus:ring-1 focus:ring-brand-gold transition-colors outline-none placeholder-gray-600"
                    placeholder="••••••••">
            </div>

            {{-- RODAPÉ DO FORM (Lembrar + Esqueci Senha) --}}
            <div class="flex items-center justify-between mt-2">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded bg-brand-black border-gray-700 text-brand-gold focus:ring-brand-gold focus:ring-offset-brand-dark cursor-pointer">
                    <span class="ml-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-brand-gold transition-colors">Manter conectado</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[10px] font-black text-gray-500 hover:text-brand-gold uppercase tracking-widest transition-colors">
                        Esqueceu a senha?
                    </a>
                @endif
            </div>

            {{-- BOTÃO ENTRAR --}}
            <div class="pt-4">
                <button type="submit" class="w-full py-4 bg-brand-gold text-brand-dark font-black uppercase tracking-widest rounded-lg text-xs hover:bg-brand-hover shadow-[0_0_20px_rgba(255,194,12,0.2)] hover:shadow-[0_0_25px_rgba(255,194,12,0.4)] transition-all transform hover:-translate-y-0.5">
                    Entrar no Sistema
                </button>
            </div>
        </form>
    </div>

</body>
</html>